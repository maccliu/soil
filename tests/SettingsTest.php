<?php


/**
 * Soil: An underlying PHP framework, under the MIT license.
 *
 * Copyright (c) 2016-2017, Macc Liu
 * WITHOUT WARRANTY OF ANY KIND
 */
use PHPUnit\Framework\TestCase;


class MoneyTest extends TestCase
{


    /**
     * new
     *
     * @covers Soil\Settings::__construct
     */
    function testClassConstruct1()
    {
        $a = new Soil\Settings;
        $this->assertInstanceOf('Soil\\Settings', $a);

        $a = new Soil\Settings();
        $this->assertInstanceOf('\Soil\\Settings', $a);
    }


    /**
     * 带参数new
     *
     * @covers Soil\Settings::__construct
     */
    function testClassConstruct2()
    {
        $a = new Soil\Settings([]);
        $this->assertInstanceOf('\Soil\\Settings', $a);

        $a = new Soil\Settings([
            'db.server'   => 'localhost',
            'db.name'     => 'root',
            'db.password' => 'password',
        ]);
        $this->assertInstanceOf('\Soil\\Settings', $a);
    }

    /**
     * 带参数new
     *
     * @covers Soil\Settings::__construct
     */
    function testClassConstructError3()
    {
        try {
            $a = new Soil\Settings([111]);
        } catch (Exception $ex) {
            return $ex instanceof \InvalidArgumentException;
        }
    }
}
