<?php

namespace corsica\regulb\salarios;

class Salario extends \corsica\regulb\DbMySql
{

  public function filtrar($filtro)
  {
    $this->logger->debug("filtrando", array("filtro" => $filtro));
    $sql = "select * from salario ";
    $sql .= "where 1=1 ";

    $tags = explode(" ", $filtro->nombre, 3);

    foreach ($tags as $t) {
      if ($t != "")
        $sql .= " and nombre like " . $this->like($t);
    }
    $tags = explode(" ", $filtro->organismo_nombre, 3);

    foreach ($tags as $t) {
      if ($t != "")
        $sql .= " and organismo_nombre like " . $this->like($t);
    }

    $sql .= " limit " . self::PAGINACION * ($filtro->pagina - 1) . "," . self::PAGINACION;

    $registros = $this->selectObj($sql);

    $totalRegistros = $this->select1("select count(id) from salario");
    return array("registros" => $registros, "totalRegistros" => $totalRegistros);
  }
  public function guardar($salario)
  {
  }
  public function guardarSet($set)
  {
    $sql = $this->insertSQL();
    $sep = " ";
    foreach ($set as $s) {
      $sql .= $sep;
      $sql .= "(" . $this->texto($s->id) . "," . $this->texto($s->tipo_contrato);
      $sql .= "," . $this->texto($s->camino) . "," . $this->texto($s->organismo_nombre) . "," . $this->texto($s->organismo_codigo);
      $sql .= "," . $this->texto($s->fecha_publicacion) . "," . $this->texto($s->anyo) . "," . $this->texto($s->mes);
      $sql .= "," . $this->texto($s->tipo_estamento) . "," . $this->texto($s->nombre) . "," . $this->texto($s->grado_eus);
      $sql .= "," . $this->texto($s->tipo_calificacionp) . "," . $this->texto($s->tipo_cargo) . "," . $this->texto($s->region);
      $sql .= "," . $this->texto($s->asignaciones) . "," . $this->texto($s->tipo_unidad_monetaria) . "," . $this->texto($s->remuneracionbruta);
      $sql .= "," . $this->texto($s->remuliquida_mensual) . "," . $this->texto($s->diurnas) . "," . $this->texto($s->nocturnas);
      $sql .= "," . $this->texto($s->festivas) . "," . $this->texto($s->fecha_ingreso) . "," . $this->texto($s->fecha_termino);
      $sql .= "," . $this->texto($s->observaciones) . "," . $this->texto($s->enlace) . "," . $this->texto($s->viaticos) . ")";
      $sep = ",";
    }
    $this->insert($sql);
    return "ok";
  }
  private function insertSQL()
  {
    $sql = "INSERT IGNORE INTO salario ";
    $sql .= "(id, tipo_contrato ";
    $sql .= ", camino, organismo_nombre, organismo_codigo ";
    $sql .= ", fecha_publicacion, anyo, mes ";
    $sql .= ", tipo_estamento, nombre, grado_eus ";
    $sql .= ", tipo_calificacionp, tipo_cargo, region ";
    $sql .= ", asignaciones, tipo_unidad_monetaria, remuneracionbruta ";
    $sql .= ", remuliquida_mensual, diurnas, nocturnas ";
    $sql .= ", festivas, fecha_ingreso, fecha_termino ";
    $sql .= ", observaciones, enlace, viaticos) VALUES ";
    return $sql;
  }
}
