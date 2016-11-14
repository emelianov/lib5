<?php
//base.php	v5.2.0

require_once(OLIB_PATH.'/config.all.php');
require_once(OLIB_PATH.'/class.FastTemplate.php');

define('OTYPE_LDAP',	1);
define('OTYPE_ORACLE',	2);
define('OTYPE_MSSQL',	3);
define('OSET_AUTO',	0);
define('OSET_SINGLE',	1);
define('OSET_MULTI',	2);
define('OID_PERSON',	1000000);
define('ON',		true);
define('OFF',		false);

define('OTPL_APPEND',	1);
global		$base_tpl;
global		$init_tpl;
$init_tpl	= array();

function &initFastTemplate($tpl=NULL) {
 global $base_tpl;
 global $init_tpl;
 if ($tpl === NULL) {
  $base_tpl	= new FastTemplate(OTPL_PATH);
 } else {
  $base_tpl	= $tpl;
 }
 $base_tpl->no_strict();
 $base_tpl->define(array(	'page'		=> 'page.tpl',
				'body'		=> 'body.login.tpl',
				'link'		=> 'html.link.tpl',
				'option'	=> 'html.option.tpl',
				'td'		=> 'html.td.tpl',
				'tr'		=> 'html.tr.tpl',
				'table'		=> 'html.table.tpl',
				'td_right'	=> 'html.td.align.right.tpl',
				'br'		=> 'html.br.tpl',
				'plain'		=> 'html.plain.tpl',
				'td_nowrap'	=> 'html.td.nowrap.tpl',
				'select'	=> 'html.select.tpl',	//5.0.2
				'ancor'		=> 'html.ancor.tpl'	//5.1.0

	    ));
 $base_tpl->assign(	array(	'DEBUG'		=> '',
				'TITLE'		=> '-',
				'INFODATE'	=> time()
			));
 foreach ($init_tpl as $func) {
  $func();
 }
 return $base_tpl;
}

function obj($id) {
}

class _Obj {
 var		$createTime	= false;
 var		$id;
 var		$_tpl;
 var		$_type;
 var		$_fields	= array();
 var		$_map		= array();
 var		$count;
 var		$_query;
 var		$_tpl_td	= 'td';
 var		$_tpl_tr	= 'tr';
 var		$_tpl_table	= 'table';
 var		$_tpl_option	= 'option';
 var		$_tpl_link	= 'link';
// var		$_tplTableRowColumnPrev = false;//5.0.2
 var		$debug		= false;	//5.0.2
 var		$utime		= 0;

 function __construct($set=OSET_AUTO) {
  global	$base_tpl;
  $this->_tpl	= & $base_tpl;
  $this->createTime = time();
  $this->count	= 0;				//5.0.2 (was = 1)
  $this->_type	= $set;
 }
 function _addField($name, $realName=false) {
  if (is_array($name)) {			//5.1.0
   foreach ($name as $n) {
    $this->_fields[]	= $n;
    $this->$n		= false;
    $this->_map[]	= $realName?($realName.'.'.$n):$n;
   }
  } else {
   $this->_fields[]	= $name;
   $this->$name		= false;
   $this->_map[]	= $realName?$realName:$name;	//5.1.0
  }
 }
 function tplSet(&$tplObject) {
  $this->_tpl	= &$tplObject;
 }
 function tplFill() {
 }
 function save() {
  return false;
 }
 function _execute($query, $fields=false) {
 }
 public function __clone() {
  foreach ($this->_fields as $name) {
   $this->$name	= $this->$name;
  }
//foreach($this as $k=>$i) {
//       $this->{$k}=clone $i;
//         }
 }
 function extract($element) {			//Buggy!!!
  $tmp		= clone $this;			//5.0.2 PHP5
  if ($this->count > 1) {
   foreach ($this->_fields as $name) {
    $tmpRow	= $tmp->$name;
    $tmp->$name	= $tmpRow[$element];
    $tmp->count	= 1;
   }
  }
  return $tmp;
 }
 function push($obj=false, $appendFields=false) {
  if ($obj) {					//5.0.2
   for ($i = 0; $i < $obj->count; $i++) {
    foreach ($obj->_fields as $item) {
     if (isset($this->$item)) {
      $t	= & $this->$item;
      $s	= & $obj->$item;
      $t[]	= $s[$i];
     }
    }
   }
  } else {					//5.0.2
   foreach ($this->_fields as $item) {
    if (isset($this->$item)) {
     $t	= & $this->$item;
     $t[]	= false;
    }
   }
  }
  $this->count++;
 }
 function _toMulti() {
  foreach ($this->_fields as $item) {
   if (isset($this->$item) && !is_array($this->$item)) {
    $tmp	= array();
    $tmp[]	= $this->$item;
    $this->$item= $tmp;
   }
  }
  $this->_type	= OSET_MULTI;
  $this->count	= 1;
 }
 function toMulti() {
  foreach ($this->_fields as $item) {
//   if (isset($this->$item) && !is_array($this->$item)) {
   if (isset($this->$item)) {			//5.1.1
    $tmp	= array();
    $tmp[]	= $this->$item;
    $this->$item= $tmp;
   } else {					//5.0.2
    $tmp	= array();
    $tmp[]	= false;
    $this->$item= $tmp;
   }
  }
  $this->_type	= OSET_MULTI;
  $this->count	= 1;
 }

 function sortBy($fieldName, $sortType=SORT_STRING, $sortFlag=SORT_ASC) {
//  $evalStr	= '@array_multisort(& $this->'.$fieldName.', $sortType, $sortFlag';
  $evalStr	= 'array_multisort($this->'.$fieldName.', $sortType, $sortFlag';	//5.1.4
  foreach ($this->_fields as $name) {
   if ($name != $fieldName) {
//    $evalStr	.= ', & $this->'.$name;
    $evalStr	.= ', $this->'.$name;							//5.1.4
   }
  }
  $evalStr	.= ');';
  eval($evalStr);
 }
 function fieldUpper($name) {
  $target	= & $this->$name;
  for ($i = 0; $i < count($target); $i++) {
   $target[$i]	= strtoupper($target[$i]);
  }
 }
 function fieldLower($name) {
  $target	= & $this->$name;
  for ($i = 0; $i < count($target); $i++) {
   $target[$i]	= strtoupper($target[$i]);
  }
 }
 function _tplClearDups($tds, $prevtds, $i) {					//5.1.0
  return	false;
 }
 function tplLink($var, $text, $value, $title='') {
  $this->_tpl->assign(array(	'LINK_VALUE'		=> $value,
				'LINK_TEXT'		=> $text,
				'LINK_TITLE'		=> $title
		));
  $this->_tpl->parse($var, '.'.$this->_tpl_link);
 }
 function tplAncor($var, $text, $name) {					//5.1.0
  $this->_tpl->assign(array(	'ANCOR_NAME'		=> $name,
				'ANCOR_TEXT'		=> $text
		));
  $this->_tpl->parse($var, '.ancor');
 }

 function tplPlainHtml($var, $value) {
  $this->_tpl->assign(array(	'PLAIN_HTML'		=> $value
		));
  $this->_tpl->parse($var, '.plain');
 }
 function tplComboboxOption($var, $value, $text, $selected=false) {
  $this->_tpl->assign(Array(	'OPTION_VALUE'		=> $value,
				'OPTION_TEXT'		=> $text,
				'OPTION_SELECTED'	=> $selected?'SELECTED':''
		));
  $this->_tpl->parse($var, '.'.$this->_tpl_option);
 }
// function tplTableInner($var, $textFields, $linkFields, $linkPrefixes, $ancor=false, $nodup=false) {
 function tplTableInner($var, $textFields, $linkFields, $linkPrefixes, $width=false, $nodup=false) {
  $ancor_symbol		= '';
  $prevtds		= false;						//5.1.0
  for ($j = 0; $j < $this->count; $j++) {
   for ($i = 0; $i < count($textFields); $i++) {
    $textFieldName = $textFields[$i];
    $linkFieldName = $linkFields[$i];
    if ($textFieldName) {
//    $textFieldsA	= ($textFieldName[0]=='&')?(& $this->$textFieldName):false;
     if (substr($textFieldName, 0, 1) == '&') {
//     $textFieldsA	= false;
      $textFieldsB	= false;
      $funcName		= '_virtual'.substr($textFieldName, 1).'toString';		//5.0.2
     } else {
      $textFieldsA	= & $this->$textFieldName;
      $textFieldsB	= true;
      $funcName		= '_'.$textFieldName.'toString';				//5.0.2
     }
     $tds[$i]['field']	= $textFieldName;
     if (method_exists($this, $funcName)) {
      $tds[$i]['text']	= $this->$funcName($textFieldsA[$j]);
     } else {
      $tds[$i]['text']	= ($textFieldsB !== false)?$textFieldsA[$j]:substr($textFields[$i], 1);
     }
     if ($linkFieldName !== false) {
//     if ($linkPrefixes[$i] !== false) {							//5.2.0
      $linkFieldsA	= & $this->$linkFieldName;
      if ($linkFieldsA[$j] !== false && $linkFieldsA[$j] != '') {
       if (strpos($linkPrefixes[$i], '_VALUE_')) {					//5.0.2
        $tds[$i]['link']	= str_replace('_VALUE_', $linkFieldsA[$j], $linkPrefixes[$i]);	//5.0.2
       } else {										//5.0.2
        $tds[$i]['link']	= $linkPrefixes[$i].$linkFieldsA[$j];
       }
       $tds[$i]['link_value']	= $linkFieldsA[$j];					//5.2.0
      } else {
       $tds[$i]['link']		= false;
       $tsd[$i]['link_value']	= false;						//5.2.0
      }
     } else {
      $tds[$i]['link']		= false;
      $tsd[$i]['link_value']	= false;						//5.2.0
     }
     $tds[$i]['value']		= $textFieldsA[$j];
     if ($width) {
      if (is_array($width)) {
       $tds[$i]['width']	= $width[$i];
      } else {
       $tds[$i]['width']	= $width;
      }
     } else {
      $tds[$i]['width']	= 1;
     }
    } else {
     $tds[$i]['text']	= '';
     $tds[$i]['value']	= '';
     $tds[$i]['link']	= false;
    }
   }
//   echo $k++.'/'.$this->count.' '.$this->cn[$j].'<BR>';
   $tplTableInnerTmp	= substr($tds[0]['value'], 0, 1);
   if ($tplTableInnerTmp <> $ancor_symbol) {
    $ancor_symbol	= $tplTableInnerTmp;
    $this->tplAncor($var, '', $ancor_symbol);
   }
   $this->tplTableRow($var, $tds, $prevtds);
   $prevtds		= $tds;								//5.1.0
  }
 }

 function tplTableInnerFast($var, $textFields, $linkFields, $linkPrefixes) {
  $r			= '';
  for ($a = 0; $a < count($textFields); $a++) {
   $field		= $textFields[$a];
   $r			.= '$tds['.$a.']["text"]=$v'.$field.'[$i];$tds['.$a.']["field"]="'.$field.'";$tds['.$a.']["link"]=false;$tds['.$a.']["value"]=false;';
   $v			.= '$v'.$field.'=& $this->'.$field.';';
  }
  $t			= $v.'for ($i = 0; $i < $this->count; $i++) {'.$r.'$this->tplTableRow($var, $tds);}';
//  eval($v);
//  for ($i = 0; $i < $this->count; $i++) {
//   eval($r);
//   echo $r.'<BR>';
//   $this->tplTableRow($var, $tds);
//  }
  eval($t);
 }

 function tplTableRow($var, $tds, $prevtds=false) {
// $tds = array();
// $tds['link']		link URL
// $tds['value']	real value
// $tds['text']		link text
// $tds['field']	field name
  $this->_tpl->clear('TR_TEXT');
  for ($i = 0; $i < count($tds); $i++) {
   $funcName           = '_'.$tds[$i]['field'].'TableRowColumn';
   if (!$this->_tplClearDups($tds, $prevtds, $i)) {					//5.1.0
    if (method_exists($this, $funcName)) {
     $this->$funcName('TR_TEXT', $tds[$i]['text'], $tds[$i]['link'], $tds[$i]['width']);
    } else {
     $this->tplTableRowColumn('TR_TEXT', $tds[$i]['text'], $tds[$i]['link'], $tds[$i]['width']);
    }
   } else {
    $this->tplTableRowColumn('TR_TEXT', '&nbsp;');
   }
  }
  $this->_tpl->parse($var, '.'.$this->_tpl_tr);
//  $this->_tpl->parse('TPL_TABLE_ROW_TMP', $this->_tpl_tr);	//5.1.0
//  $this->_tpl->assign($var, $this->_tpl->get_assigned($var).$this->_tpl->get_assigned('TPL_TABLE_ROW_TMP')); //5.1.0
 }

 function tplTableRowFast($var, $tds) {
// $tds = Array();
// $tds['link']		link URL
// $tds['value']	real value
// $tds['text']		link text
// $tds['field']	field name
  $this->_tpl->clear('TR_TEXT');
  for ($i = 0; $i < count($tds); $i++) {
//   $funcName           = '_'.$tds[$i]['field'].'TableRowColumn';
//   if (method_exists($this, $funcName)) {
//    $this->$funcName('TR_TEXT', $tds[$i]['text'], $tds[$i]['link']);
//   } else {
    $this->tplTableRowColumn('TR_TEXT', $tds[$i]['text'], $tds[$i]['link']);
//   }
  }
  $this->_tpl->parse($var, '.'.$this->_tpl_tr);
 }

 function tplTableRowColumn($var, $text, $value=false, $width='') {
  $this->_tpl->clear('TD_TEXT');
  if ($value !== false) {
   $this->tplLink('TD_TEXT', $text, $value);
  } else {
   $this->_tpl->assign('TD_TEXT', $text);
  }
  $this->_tpl->assign('TD_EXTRA', $width);
  $tmp			= $this->_tpl->utime();
  $this->_tpl->parse($var, '.'.$this->_tpl_td);
  $this->utime		+= ($this->_tpl->utime() - $tmp);
 }

 function tplComboboxInner($var, $textField, $valueField, $reset=OTPL_APPEND) {
  $tpl		= & $this->_tpl;
  if ($reset !== OTPL_APPEND) {
   $this->_tpl->assign($var, '');
  }
  if ($this->count > 1 || $this->_type == OSET_MULTI) {
   $this->_tpl->clear($var);
   $values	= & $this->$valueField;
   $texts	= & $this->$textField;
   for ($i = 0; $i < $this->count; $i++) {
    $this->tplComboboxOption($var, $values[$i], $texts[$i]);
   }
  } else {
   $this->tplComboboxOption($var, $this->$valueField, $this->$textField);
  }
 }
 
 function search($field, $value) {		//5.0.2
  return array_search($value, $this->$field);	//5.1.0
/*
  $sf		= $this->$field;
  for ($i = 0; $i < count($sf); $i++) {
   if ($sf[$i] === $value) {
    return $i;
   }
  }
  return false;
*/
 }
 
 function delete($i, $count=1) {		//5.0.2
  foreach ($this->_fields as $item) {
//   @array_splice(& $this->$item, $i, $count);
    array_splice($this->$item, $i, $count);	//5.1.4
  }
  $this->count	= $this->count - $count;
 }
 
 function unixTimestampToString($t) {
  if (is_null($t)) {				//5.1.3
   return '-';
  } else {
   return date('Y-m-d H:i:s', $t);
  }
 }
 
 function text2html($s) {			//5.1.2
  return nl2br(htmlspecialchars($s));
 }
}

?>