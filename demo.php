<?php

require_once(__DIR__."/vendor/autoload.php");

// Create a new uploader
$up = new \FlSouto\HtUpload($fieldname = "attachment", $savedir=__DIR__."/tests/");

// Add Validations
$up->required('Please choose a file.');
$up->accept(['application/pdf'], $msg = "Only pdf is allowed!");
$up->context($_REQUEST);

// Submit processing
if($_SERVER['REQUEST_METHOD']=='POST'){
    $result = $up->process();
    if($result->error){
        $up->error(true);
    }
    if($result->output){
        echo "File uploaded successfully: $result->output";
    }

}

// UI customizations
$up->label(["text"=>"Pick a file from your computer","selected"=>"Selected file: %s"]);
//$up->attrs(['onChange'=>'this.form.submit()']);
?>

<form method="POST" enctype="multipart/form-data">
    <?php echo $up; ?>
    <button>Submit</button>
</form>
