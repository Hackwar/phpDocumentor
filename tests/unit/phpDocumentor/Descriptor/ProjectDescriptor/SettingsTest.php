<?php

declare(strict_types=1);

/**
 * This file is part of phpDocumentor.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @link http://phpdoc.org
 */

namespace phpDocumentor\Descriptor\ProjectDescriptor;

use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \phpDocumentor\Descriptor\ProjectDescriptor\Settings
 * @covers ::<private>
 */
final class SettingsTest extends TestCase
{
    /**
     * @covers ::getMarkers
     * @covers ::setMarkers
     */
    public function testKeepingTrackOfMarkers() : void
    {
        $expected = ['TODO', 'FIXME'];

        $settings = new Settings();

        $this->assertSame([], $settings->getMarkers());

        $settings->setMarkers($expected);

        $this->assertSame($expected, $settings->getMarkers());
    }

    /**
     * @covers ::shouldIncludeSource
     * @covers ::includeSource
     * @covers ::excludeSource
     */
    public function testItCanKeepTrackWhetherSourceIsIncluded() : void
    {
        $settings = new Settings();

        $this->assertFalse($settings->shouldIncludeSource());

        $settings->includeSource();

        $this->assertTrue($settings->shouldIncludeSource());

        $settings->excludeSource();

        $this->assertFalse($settings->shouldIncludeSource());
    }

    /**
     * @covers ::isModified
     */
    public function testDetectSettingsAreModifiedWhenChangingInclusionOfSource() : void
    {
        $settings = new Settings();

        $this->assertFalse($settings->isModified());

        $settings->includeSource();

        $this->assertTrue($settings->isModified());
    }
    /**
     * @covers ::getVisibility
     * @covers ::setVisibility
     */
    public function testItCanKeepTrackWhetherVisibilityIsSpecified() : void
    {
        $settings = new Settings();

        $this->assertSame(Settings::VISIBILITY_DEFAULT, $settings->getVisibility());

        $settings->setVisibility(Settings::VISIBILITY_PUBLIC);

        $this->assertSame(Settings::VISIBILITY_PUBLIC, $settings->getVisibility());
    }

    /**
     * @covers ::isModified
     */
    public function testDetectSettingsAreModifiedWhenChangingVisibility() : void
    {
        $settings = new Settings();

        $this->assertFalse($settings->isModified());

        $settings->setVisibility(Settings::VISIBILITY_PUBLIC);

        $this->assertTrue($settings->isModified());
    }

    /**
     * @covers ::clearModifiedFlag
     */
    public function testThatTheModifiedFlagCanBeReset() : void
    {
        $settings = new Settings();

        $this->assertFalse($settings->isModified());

        $settings->includeSource();

        $this->assertTrue($settings->isModified());

        $settings->clearModifiedFlag();

        $this->assertFalse($settings->isModified());
    }
}
