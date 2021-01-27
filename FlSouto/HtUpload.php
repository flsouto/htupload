<?php
namespace FlSouto;

class HtUpload extends HtWidget{

    protected $accept = [];
    protected $accept_errmsg = '';
    protected $required_errmsg = '';
    protected $savedir = '';
    protected $selected_text = '%s';

    function __construct($name, $savedir)
    {
        parent::__construct($name);
        $this->savedir = $savedir;
        $this->label_attrs['style']['cursor'] = 'pointer';

    }

    function savedir(){
        return $this->savedir;
    }

    function original(){
        if($value = $this->value()){
            // remove extension
            if($ext = pathinfo($value, PATHINFO_EXTENSION)){
                $value = str_replace($ext, '', $value);
            }
            // remove timestamp and decode original name
            return base64_decode(substr($value,10));
        }
        return null;
    }

    function label($label){
        if(is_array($label) && isset($label['selected'])){
            $this->selected_text = $label['selected'];
            unset($label['selected']);
        }
        if(!empty($label)){
            parent::label($label);
        }
        return $this;
    }

    function validate(){
        return $this->process()->error;
    }

    function required($errmsg = 'Please choose a file.'){
        $this->required_errmsg = $errmsg;
        return $this;
    }

    function process($force = false)
    {
        if($this->result && !$force){
            return $this->result;
        }

        $result = parent::process();
        $result->output = '';
        $result->error = '';

        $key = $this->getSubmitFlag();

        if(!isset($_FILES[$key])){
            return $result;
        }

        $persisted = $this->param->process($this->context)->output;

        if(!empty($_FILES[$key]['error']) && $_FILES[$key]['error'] != 4){
            $result->output = $persisted;
            $result->error = 'Upload error: '.$_FILES[$key]['error'];
            return $result;
        }

        if(empty($_FILES[$key]['tmp_name'])){
            if($persisted){
                $result->output = $persisted;
            }
            else if($this->required_errmsg){
                $result->error = $this->required_errmsg;
            }
            return $result;
        }

        if($this->accept){
            $mime = mime_content_type($_FILES[$key]['tmp_name']);
            if(!in_array($mime, $this->accept)){
                $result->error = $this->accept_errmsg;
                return $result;
            }
        }

        $ext = pathinfo($_FILES[$key]['name'],PATHINFO_EXTENSION);
        $save_as = time().base64_encode($_FILES[$key]['name']);
        if($ext){
            $save_as .= '.'.$ext;
        }

        copy($_FILES[$key]['tmp_name'],$this->savedir.'/'.$save_as);
        if($persisted && file_exists($this->savedir.'/'.$persisted)){
            unlink($this->savedir.'/'.$persisted);
        }

        $result->output = $save_as;

        return $result;
    }

    function accept(array $mimes, $errmsg='Unsupported file format'){
        $this->accept = $mimes;
        $this->accept_errmsg = $errmsg;
        return $this;
    }

    protected function renderUploader(){
        // Render file uploader input
        $this->attrs['type'] = 'file';
        $this->attrs['name'] = $this->getSubmitFlag();

        if($this->label_text){
            $this->attrs['style'] = 'display:none';
        }
        echo "<input $this->attrs>";
    }

    protected function renderUploaderLabel(){
        // Render label / handler / indicator
        $tmp = $this->label_text;
        $this->label_text = $this->original()
            ? sprintf($this->selected_text, $this->original())
            : $this->label_text;
        $this->renderLabel();
        $this->label_text = $tmp; // restore
        // little snippet to update selected file
        $update = json_encode($this->selected_text).".replace('%s', this.value)";
        echo "<script>document.getElementById('{$this->id()}').addEventListener('change',function(){ document.querySelector('label[for='+this.id+']').innerHTML = $update; });</script>";

    }

    protected function renderUploaderHiddenInput(){
        // Render hidden input with uploaded filename
        $attrs = new HtAttrs(['type'=>'hidden','name'=>$this->name(),'value'=>$this->value()]);
        echo "<input $attrs />";
    }

    protected function renderWritable()
    {
        $this->renderUploader();
        $this->renderUploaderLabel();
        $this->renderUploaderHiddenInput();
    }

    protected function renderReadonly()
    {
        $this->renderUploaderLabel();
        $this->renderUploaderHiddenInput();
    }

    function renderInner(){
        if($this->readonly){
            $this->renderReadonly();
        } else {
            $this->renderWritable();
            $this->renderError();
        }
    }

}
