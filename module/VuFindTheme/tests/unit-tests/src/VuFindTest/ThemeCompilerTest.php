<?php
/**
 * ThemeCompiler Test Class
 *
 * PHP version 5
 *
 * Copyright (C) Villanova University 2017.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License version 2,
 * as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301  USA
 *
 * @category VuFind
 * @package  Tests
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     https://vufind.org/wiki/development:testing:unit_tests Wiki
 */
namespace VuFindTest;
use VuFindTheme\ThemeCompiler;
use VuFindTheme\ThemeInfo;

/**
 * ThemeCompiler Test Class
 *
 * @category VuFind
 * @package  Tests
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     https://vufind.org/wiki/development:testing:unit_tests Wiki
 */
class ThemeCompilerTest extends Unit\TestCase
{
    /**
     * Path to theme fixtures
     *
     * @var string
     */
    protected $fixturePath;

    /**
     * ThemeInfo object for tests
     *
     * @var ThemeInfo
     */
    protected $info;

    /**
     * Path where new theme will be created
     *
     * @var string
     */
    protected $targetPath;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->fixturePath = realpath(__DIR__ . '/../../fixtures/themes');
        $this->info = new ThemeInfo($this->fixturePath, 'parent');
        $this->targetPath = $this->info->getBaseDir() . '/compiled';
    }

    /**
     * Standard setup method.
     *
     * @return void
     */
    public function setUp()
    {
        // Give up if the target directory already exists:
        if (is_dir($this->targetPath)) {
            return $this->markTestSkipped('compiled theme already exists.');
        }
    }

    /**
     * Test the compiler.
     *
     * @return void
     */
    public function testCompiler()
    {
        $baseDir = $this->info->getBaseDir();
        $parentDir = $baseDir . '/parent';
        $childDir = $baseDir . '/child';
        $compiler = $this->getThemeCompiler();
        $result = $compiler->compile('child', 'compiled');

        // Did the compiler report success?
        $this->assertEquals('', $compiler->getLastError());
        $this->assertTrue($result);

        // Was the target directory created with the expected files?
        $this->assertTrue(is_dir($this->targetPath));
        $this->assertTrue(file_exists("{$this->targetPath}/parent.txt"));
        $this->assertTrue(file_exists("{$this->targetPath}/child.txt"));

        // Did the right version of the  file that exists in both parent and child
        // get copied over?
        $this->assertEquals(
            file_get_contents("$childDir/js/hello.js"),
            file_get_contents("{$this->targetPath}/js/hello.js")
        );
        $this->assertNotEquals(
            file_get_contents("$parentDir/js/hello.js"),
            file_get_contents("{$this->targetPath}/js/hello.js")
        );

        // Did the configuration merge correctly?
        $expectedConfig = [
            'extends' => false,
            'css' => ['child.css'],
            'js' => ['hello.js', 'extra.js'],
            'helpers' => [
                'factories' => [
                    'foo' => 'fooOverrideFactory',
                    'bar' => 'barFactory',
                ],
                'invokables' => [
                    'xyzzy' => 'Xyzzy',
                ]
            ],
        ];
        $mergedConfig = include "{$this->targetPath}/theme.config.php";
        $this->assertEquals($expectedConfig, $mergedConfig);
    }

    /**
     * Teardown method: clean up test directory.
     *
     * @return void
     */
    public function tearDown()
    {
        $this->getThemeCompiler()->removeTheme('compiled');
    }

    /**
     * Get a test ThemeCompiler object
     *
     * @return ThemeInfo
     */
    protected function getThemeCompiler()
    {
        return new ThemeCompiler($this->info);
    }
}
