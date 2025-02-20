<?php

namespace TeamWorkPm\Tests;

use TeamWorkPm\Exception;
use TeamWorkPm\Factory;

class MeTest extends TestCase
{
    private $model;

    public function setUp()
    {
        parent::setUp();
        $this->model = Factory::build('me');
    }

    /**
     * @test
     */
    public function get()
    {
        try {
            $me = $this->model->get();
            $this->assertEquals('loduis@gmail.com', $me->userName);
        } catch (Exception $e) {
            $this->fail($e->getMessage());
        }
    }
}