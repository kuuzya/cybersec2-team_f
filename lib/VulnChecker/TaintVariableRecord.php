<?php
namespace VulnChecker;

// 汚染レベル
// DIRTY 危険、$_GET などを直接用いている
const TAINT_DITRY = 20;
// MAYBE 外部関数呼び出しの返り値や解決できない変数などを用いている
const TAINT_MAYBE = 10; 
// CLEAN 文字列リテラル あるいは shellescape などを施している
const TAINT_CLEAN = 0;

class TaintVariableRecord
{
  private $vars = array();

  public function set($name, $type){
    $this->vars[$name] = $type;
  }

  public function get($name, $type){
    return $this->vars[$name];
  }

  public static function createGlobalRecord(){
    $ret = new TaintVariableRecord();
    $taint_vars =  array(
      '_GET', '_SET', '_REQUEST', '_COOKIE', '_FILES', 'argv'
    );
    foreach($taint_vars as $v) {
      $ret->set($v, TAINT_DIRTY);
    }
    return $ret;
  }
}