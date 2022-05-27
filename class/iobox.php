<?php
/*
     Copyright (C) 2007 - 2008  Nicaw

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License along
    with this program; if not, write to the Free Software Foundation, Inc.,
    51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
*/

class IOBox
{
private $elements, $buttons, $name, $label;
public $target, $icon;

function __construct($name){
	$this->name = $name;
	$buttons = 0;
}
public function addMsg($msg){
	$this->elements[]= '<p style="margin: 5px">'.$msg.'</p>';
}
public function addSelect($name,$options){
	$code = '<select id="'.$this->name.'__'.$name.'" name="'.$this->name.'__'.$name.'">';
	foreach (array_keys($options) as $option){
		$code.= '<option value="'.$option.'">'.$options[$option].'</option>';
	}
	$code.= '</select>&nbsp;<label for="'.$this->name.'__'.$name.'">- '.ucfirst($name).'</label>';
	$this->elements[] = $code;
}
public function addCaptcha(){
	global $cfg;
	if(!$cfg['use_captcha']) return;
	if (isset($_POST['ajax']))
		$img = 'doimg.php?'.time();
	else
		$img = '../doimg.php?'.time();
	$_SESSION['RandomText'] = substr(str_shuffle(strtolower('qwertyuipasdfhjklzxcvnm12345789')), 0, 6);
	$this->elements[]= '<img width="250px" height="40px" src="'.$img.'" alt="Verification Image"/>';
	$this->elements[]= '<input id="captcha" name="'.$this->name.'__captcha" type="text" maxlength="10" style="text-transform: uppercase"/>&nbsp;<label for="captcha">- Verification</label>';
}
public function addInput($name, $type = 'text', $value = '', $length = 100, $readonly = false){
	if ($readonly) $readonly = ' readonly="readonly"';
	else $readonly = '';
	$this->elements[]= '
	<div class="wrapper-form">
		<label for="'.$this->name.'__'.$name.'">'.ucfirst($name).'</label>
		<input class="textfield" id="'.$this->name.'__'.$name.'" name="'.$this->name.'__'.$name.'" type="'.$type.'" maxlength="'.$length.'" value="'.$value.'"'.$readonly.'/>
	</div>
	';
}
public function addCheckBox($name, $check = false){
	if ($check) $check = ' checked="checked"';
	else $check = '';
	$this->elements[]= '<input type="checkbox" id="'.$this->name.'__'.$name.'" name="'.$this->name.'__'.$name.'"'.$check.'>&nbsp;<label for="'.$this->name.'__'.$name.'">'.ucfirst($name).'</label>';
}
public function addTextbox($name,$value = '',$cols = 40,$rows = 10){
	$this->elements[]= '<textarea name="'.$this->name.'__'.$name.'" cols="'.$cols.'" rows="'.$rows.'">'.$value.'</textarea>';
}
public function addSubmit($text){
	$this->buttons[]= '
		<div class="wrapper-actions">
			<input class="btn-primary" type="submit" name="'.$this->name.'__'.$this->name.'" value="'.$text.'"/>
		</div>		
		';
}
public function addReload($text){
	$this->buttons[]= '<input style="width: 100px; height: 25px;" onclick="ajax(\'form\',\''.htmlspecialchars($_SERVER['PHP_SELF']).'\',\'\',true)" type="button" name="'.$this->name.'__'.$this->name.'" value="'.$text.'"/>';
}
public function addRefresh($text){
	$this->buttons[]= '<input onclick="location.reload(false)" type="button" style="width: 100px; height: 25px;" value="'.$text.'"/>';
}
public function addClose($text){
	$this->buttons[]= '<input style="width: 100px; height: 25px;" onclick="document.getElementById(\'iobox\').style[\'visibility\'] = \'hidden\'" type="button" value="'.$text.'"/>';
}
public function addCode($code){
	$this->elements[]= $code;
}
public function addLabel($code){
	$this->elements[] = '<h1>'.$code.'</h1>';
}
public function getCode(){
	$code = '';
	if (isset($_POST['ajax']))
		$code = '<form id="'.$this->name.'" action="javascript:ajax(\'wrapper-register\',\''.htmlspecialchars($this->target).'\',getParams(document.getElementById(\''.$this->name.'\')),true)" method="post" >';
	else
		$code = '<div id="iobox" class="iobox"><fieldset>'.$this->label.'<form id="'.$this->name.'" action="'.htmlspecialchars($this->target).'" method="post">';
	
	foreach ($this->elements as $element)
		$code.= $element;
	// $code.= '<hr style="margin: 10px 2px 2px 2px; padding: 0;"/> | ';
	foreach ($this->buttons as $button)
		$code.= $button;

	$code.= '</form>';
	return $code;
}
public function show(){
	echo $this->getCode();
}
}

class Form
{
public $attrs;
public function __construct($name){
		foreach( array_keys($_POST) as $key){
			if (preg_match('/^'.$name.'__/',$key)) {
				$p = explode('__', $key);
				$this->attrs[$p[1]] = trim($_POST[$key]);
			}
		}
}
public function getBool($attr){
	return $this->attrs[$attr] === 'on';
}
public function exists(){
	if (isset($this->attrs)) return true;
	else return false;
}
public function validated(){
	global $cfg;
	if (!$cfg['use_captcha']) return true;
	if (strtolower($this->attrs['captcha']) === $_SESSION['RandomText'] && !empty($_SESSION['RandomText'])){
		$_SESSION['RandomText'] = null;
		return true;
	}else return false;
}
}
