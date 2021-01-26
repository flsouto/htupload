<?php
namespace FlSouto;

class HtUpload extends HtWidget{

    protected $accept = [];
    protected $accept_errmsg = '';
    protected $savedir = '';
    protected $selected_text = '%s';

    function __construct($name, $savedir)
    {
        parent::__construct($name);
        $this->savedir = $savedir;
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

    function process($force = false)
    {
        if($this->result && !$force){
            return $this->result;
        }

        $result = parent::process();
        $result->output = '';
        $result->error = '';

        $key = $this->getSubmitFlag();

        if(empty($_FILES[$key]['tmp_name'])){
           return $result;
        }

        if($this->accept){
            $mime = mime_content_type($_FILES[$key]['tmp_name']);
            if(!in_array($mime, $this->accept)){
                $result->error = $this->accept_errmsg;
            }
        } else {
            $ext = pathinfo($_FILES[$key]['original_name'],PATHINFO_EXTENSION);
            $save_as = time().base64_encode($_FILES[$key]['original_name']);
            if($ext){
                $save_as .= '.'.$ext;
            }
            copy($_FILES[$key]['tmp_name'],$this->savedir.'/'.$save_as);
            $result->output = $save_as;
        }

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
        if($this->accept){
            $this->attrs['accept'] = implode(',', $this->accept);
        }
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
        }
    }

}
