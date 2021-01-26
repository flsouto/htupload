# HtUpload

Plugin for flsouto/htform which enables file uploads.

## Usage

Creating and rendering:
```php
<?php

$up = new HtUpload("attachment",$savedir=__DIR__);
$up->label($label="Choose a file");
// echo $up to output the widget

```

Processing the submit:
```php
<?php

$up = new HtUpload("attachment",__DIR__);
$up->label(['text'=>'Choose a file','selected'=>'Attachment: %s']);
$result = $up->process(); // $result->output contains filename

```

Add validations:
```php
<?php

$up = new HtUpload("attachment",__DIR__);
$up->accept(['application/pdf','application/doc'],'Only pdf/doc is allowed!');
$result = $up->process(); // result->error contains error message

```
```php
<?php

$up = new HtUpload("attachment",__DIR__);
$up->required("It's mandatory to send a file");
$result = $up->process(); // result->error contains error message

```
