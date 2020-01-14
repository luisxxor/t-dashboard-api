<?php

namespace App\Lib\Handlers;

use Box\Spout\Common\Entity\Style\Border;
use Box\Spout\Common\Entity\Style\Color;
use Box\Spout\Writer\Common\Creator\Style\BorderBuilder;
use Box\Spout\Writer\Common\Creator\Style\StyleBuilder;

/**
 *
 */
class SpoutHandler
{
    function __construct() {}

    /**
     * Get default style.
     *
     * @return Box\Spout\Common\Entity\Style\Style
     */
    public static function getDefaultStyle()
    {
        $defaultStyle = ( new StyleBuilder() )
            ->setFontName( 'Trebuchet MS' )
            ->setFontSize( 10 )
            ->setBorder( self::getDefaultBorder() )
            ->build();

        return $defaultStyle;
    }

    /**
     * Get header style.
     *
     * @return Box\Spout\Common\Entity\Style\Style
     */
    public static function getHeaderStyle()
    {
        $lightBlueCustom = Color::rgb( 155, 194, 230 );

        $headerStyle = ( new StyleBuilder() )
            ->setBackgroundColor( $lightBlueCustom )
            ->setBorder( self::getDataBorder() )
            ->build();

        return $headerStyle;
    }

    /**
     * Get body style.
     *
     * @return Box\Spout\Common\Entity\Style\Style
     */
    public static function getBodyStyle()
    {
        $bodyStyle = ( new StyleBuilder() )
            ->setBorder( self::getDataBorder() )
            ->build();

        return $bodyStyle;
    }

    /**
     * Get default border.
     *
     * @return Box\Spout\Common\Entity\Style\Border
     */
    public static function getDefaultBorder()
    {
        $border = ( new BorderBuilder() )
            ->setBorderTop( Color::WHITE, Border::WIDTH_THIN, Border::STYLE_SOLID )
            ->setBorderRight( Color::WHITE, Border::WIDTH_THIN, Border::STYLE_SOLID )
            ->setBorderBottom( Color::WHITE, Border::WIDTH_THIN, Border::STYLE_SOLID )
            ->setBorderLeft( Color::WHITE, Border::WIDTH_THIN, Border::STYLE_SOLID )
            ->build();

        return $border;
    }

    /**
     * Get data border.
     *
     * @return Box\Spout\Common\Entity\Style\Border
     */
    public static function getDataBorder()
    {
        $border = ( new BorderBuilder() )
            ->setBorderTop( Color::BLACK, Border::WIDTH_THIN, Border::STYLE_DASHED )
            ->setBorderRight( Color::BLACK, Border::WIDTH_THIN, Border::STYLE_DASHED )
            ->setBorderBottom( Color::BLACK, Border::WIDTH_THIN, Border::STYLE_DASHED )
            ->setBorderLeft( Color::BLACK, Border::WIDTH_THIN, Border::STYLE_DASHED )
            ->build();

        return $border;
    }
}