<?php


use PHPUnit\Framework\TestCase;
use FlSouto\HtUpload;

require_once('vendor/autoload.php');

class HtUploadTest extends TestCase{


    function testNoFileSelected(){

        #mdx:1
        $up = new HtUpload("attachment",$savedir=__DIR__);
        $up->label($label="Choose a file");
        // echo $up to output the widget
        #/mdx
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

        #mdx:2
        $up = new HtUpload("attachment",__DIR__);
        $up->label(['text'=>'Choose a file','selected'=>'Attachment: %s']);
        $result = $up->process(); // $result->output contains filename
        #/mdx

        $this->assertNotEmpty($result->output);
        $this->assertEquals($up->original(), $_FILES[$key]['original_name']);

        $output = $up->__toString();
        $this->assertNotContains('Choose a file', $output);
        $this->assertContains('Attachment: '.$_FILES[$key]['original_name'], $output);
        $this->assertEmpty($result->error);

    }

    function testAcceptValidation(){

        file_put_contents($tmpf =__DIR__."/MyDocument.txt", "This is a test");

        $key = 'attachment_submit';

        $_FILES[$key]['tmp_name'] = $tmpf;
        $_FILES[$key]['original_name'] = 'MyDocument.txt';

        #mdx:3
        $up = new HtUpload("attachment",__DIR__);
        $up->accept(['application/pdf','application/doc'],'Only pdf/doc is allowed!');
        $result = $up->process(); // result->error contains error message
        #/mdx

        $this->assertNotEmpty($result->error);

        $up->accept(['text/plain']);
        $result = $up->process(true);

        $this->assertEmpty($result->error);

    }

    function testRequiredValidation(){

        file_put_contents($tmpf =__DIR__."/MyDocument.txt", "This is a test");

        $key = 'attachment_submit';

        $_FILES[$key] = null;

        #mdx:4
        $up = new HtUpload("attachment",__DIR__);
        $up->required("It's mandatory to send a file");
        $result = $up->process(); // result->error contains error message
        #/mdx

        $this->assertNotEmpty($result->error);

    }
}
