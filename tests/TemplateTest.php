<?php

use PHPUnit\Framework\TestCase;

class TemplateTest extends TestCase {

    public function testVarFromStringTpl() {
        $tpl = new phyrex\Template("<{var::test}>");
        $tpl->test = "test";

        $this->assertEquals("test", (String)$tpl);
    }

    public function testArrayFromStringTpl() {
        $tpl = new phyrex\Template("<{array::testArray::hi there @name}>");
        $tpl->push("testArray", [ "name" => "friend" ]);
        $tpl->push("testArray", [ "name" => "bob" ]);

        $this->assertEquals("hi there friendhi there bob", (String)$tpl);
    }

    public function testIncludeFromStringTpl() {
        chdir(dirname(__DIR__)."/build");
        $tpl = new phyrex\Template("<{include::include}>");
        $this->assertEquals("test", (String)$tpl);
    }
}