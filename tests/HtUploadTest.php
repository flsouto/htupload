<?php


use PHPUnit\Framework\TestCase;
use FlSouto\HtUpload;

require_once('vendor/autoload.php');

class HtUploadTest extends TestCase{


    function testNoFileSelected(){

        $up = new HtUpload("attachment",__DIR__."/data/");
        $up->label($label="Choose a file");
        $output = $up->__toString();

        $this->assertContains('type="hidden"',$output);
        $this->assertContains('type="file"',$output);
        $this->assertContains($label, $output);

    }

    function testSubmit(){

        file_put_contents($tmpf =__DIR__."/MyDocument.txt", "This is a test");

        $key = 'attachment_submit';

        $_FILES[$key]['tmp_name'] = $tmpf;
        $_FILES[$key]['original_name'] = 'MyDocument.txt';

        $up = new HtUpload("attachment",__DIR__);
        $result = $up->process();

        $this->assertNotEmpty($result->output);
        $this->assertEquals($up->original(), $_FILES[$key]['original_name']);

        $up->label(['text'=>'Choose a file','selected'=>'Attachment: %s']);
        $output = $up->__toString();
        $this->assertNotContains('Choose a file', $output);
        $this->assertContains('Attachment: '.$_FILES[$key]['original_name'], $output);

    }


}
