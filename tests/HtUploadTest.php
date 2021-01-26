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
        $_FILES[$key]['name'] = 'MyDocument.txt';

        #mdx:2
        $up = new HtUpload("attachment",__DIR__);
        $up->label(['text'=>'Choose a file','selected'=>'Attachment: %s']);
        $result = $up->process(); // $result->output contains filename
        #/mdx

        $this->assertNotEmpty($result->output);
        $this->assertEquals($up->original(), $_FILES[$key]['name']);

        $output = $up->__toString();
        $this->assertNotContains('Choose a file', $output);
        $this->assertContains('Attachment: '.$_FILES[$key]['name'], $output);
        $this->assertEmpty($result->error);

    }

    function testAcceptValidation(){

        file_put_contents($tmpf =__DIR__."/MyDocument.txt", "This is a test");

        $key = 'attachment_submit';

        $_FILES[$key]['tmp_name'] = $tmpf;
        $_FILES[$key]['name'] = 'MyDocument.txt';

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

        $_FILES[$key] = [];

        #mdx:4
        $up = new HtUpload("attachment",__DIR__);
        $up->required("It's mandatory to send a file");
        $result = $up->process(); // result->error contains error message
        #/mdx

        $this->assertNotEmpty($result->error);

    }

    function testUploadPersistence()
    {


        file_put_contents($tmpf =__DIR__."/MyDocument.txt", "This is a test");

        $key = 'attachment_submit';

        // Upload for the first time
        $_FILES[$key]['tmp_name'] = $tmpf;
        $_FILES[$key]['name'] = 'MyDocument.txt';
        $up = new HtUpload("attachment",__DIR__);
        $result = $up->process();

        // Assert that the redisplayed form contains the uploaded file name (to be resent on form submit)
        $output = $up->__toString();
        $this->assertContains($result->output, $output);

        // Simulate form is submitted again, without picking any file this time
        $_FILES[$key] = [];
        $up->context([
            'attachment' => $result->output
        ]);
        $result2 = $up->process(true);

        // Assert that the result is the same (previously uploaded file remains)
        $this->assertEquals($result->output, $result2->output);
        $this->assertFileExists(__DIR__."/".$result2->output);

        // Simulate new upload, make sure old file gets deleted
        file_put_contents($tmpf =__DIR__."/MyDocument2.txt", "This is another test");
        $_FILES[$key]['tmp_name'] = $tmpf;
        $_FILES[$key]['name'] = 'MyDocument2.txt';
        $result3 = $up->process(true);

        $this->assertNotEquals($result3->output, $result->output);
        $this->assertFileNotExists(__DIR__.'/'.$result->output);

    }
}
