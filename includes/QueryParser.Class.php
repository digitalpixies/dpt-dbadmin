<?php
class QueryParser {
  protected $structure;
  function Query() {
  }
  public static function FromStructure($structure) {
    $queryParser = new QueryParser();
    $queryParser->structure=$structure;
    $queryParser->structure["page_size"]=intval($queryParser->structure["page_size"]);
    $queryParser->structure["offset"]=intval($queryParser->structure["offset"]);
    return $queryParser;
  }
  public function GetSelectColumns() {
    $columns="";
    $firstEntry=true;
    foreach($this->structure["columns"] as $column) {
      if($firstEntry)
        $firstEntry=false;
      else
        $columns .=", ";
      $columns .=$column["id"];
    }
    return $columns;
  }
  public function GetTable() {
    return $this->structure["tablename"];
  }
  public function GetLimit() {
    if(!empty($this->structure["page_size"])) {
      $limitclause=" LIMIT ".$this->structure["page_size"];
      if(!empty($this->structure["offset"])) {
        $limitclause.=" OFFSET ".$this->structure["offset"];
      }
      return $limitclause;
    }
    return "";
  }
  public function toString() {
    return "SELECT "
      .$this->GetSelectColumns()
      ." FROM "
      .$this->GetTable()
      .$this->GetLimit();
  }
}
