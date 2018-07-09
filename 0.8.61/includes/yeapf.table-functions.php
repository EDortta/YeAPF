<?php
/*
    includes/yeapf.table-functions.php
    YeAPF 0.8.61-12 built on 2018-07-09 16:23 (-3 DST)
    Copyright (C) 2004-2018 Esteban Daniel Dortta - dortta@yahoo.com
    2018-05-30 11:21:05 (-3 DST)
*/
          function doTableColumns($dCols, $colFormat, $colDef, &$xx, $_TH_)
          {
            global $curCol, $pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave, $valores, $cell_attributes;

            $r='<tr>';
            if ($xx==0)
              $r.='<td bgcolor="#ffFFff"></td>';

            foreach($dCols as $k => $v) {
              if (is_array($v))
                doTableColumns($v, $colFormat, $colDef);
              else {
                $curCol=$xx;
                $ndx="col_$xx";
                $valores[$ndx]=$dCols[$ndx];   // acrescento para que possa ser achado depois
                $cell_attributes='';
                $vr="$colFormat($ndx)";
                $vr=analisarString($vr,$pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave, $valores);
                $cDef=unquote(analisarString($colDef,$pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave, $valores));
                $r.="<$_TH_ $cDef $cell_attributes>$vr</$_TH_>";
                $xx++;
              }
            }

            $r.='</tr>';
            unset($curCol);
            return $r;
          }


          function doTableCells($dCols, $xx, $yy, $formatCell, $evento, $tableName)
          {
            global $curCol,
                   $pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave, $valores,
                   $cell_attributes,
                   $calcValues;
            $r='';
            foreach($dCols as $k => $v) {
              $curCol=$xx;
              $cell_attributes='';
              if ($formatCell=='') {
                $cell=$calcValues[$xx][$yy];
              } else {
                $cell="$formatCell($evento,$tableName,$xx,$yy)";
                $cell=analisarString($cell,$pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave, $valores);
              }
              $r.="<td $cell_attributes id='$tableName"."_$xx"."_$yy'>$cell</td>";
              $xx++;
           }
           return $r;
          }

          function doTableRows($dCols, $dRows, $rowFormat, $rowDef, &$yy, $_TH_, $formatCell, $evento, $tableName)
          {
            global $curRow, $curCol,
                   $pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave, $valores,
                   $cell_attributes;

            $r ='';
            foreach($dRows as $k => $v) {
              if (is_array($v)) {
                $r.=doTableRows($dCols, $v, $rowFormat, $rowDef, $yy, $_TH_, $formatCell, $evento, $tableName);
              } else {
                $curRow=$yy;
                $ndx="row_$yy";
                $valores[$ndx]=$dRows[$k];   // acrescento para que possa ser achado depois
                $cell_attributes='';
                $vr="$rowFormat($ndx)";
                $vr=analisarString($vr,$pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave, $valores);
                $rDef=unquote(analisarString($rowDef,$pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave, $valores));
                $r.="\n\t<tr>\n\t\t<$_TH_ $rDef $cell_attributes>$vr</$_TH_>";
                $xx=0;
                $r.=doTableCells($dCols, $xx, $yy, $formatCell, $evento, $tableName);
                $yy++;
              }
            }

            unset($curRow);
            return $r;
          }

          function doCalcCol($calcCell, $evento, $tableName, $xx, $dRows, &$yy, $totalizador=-1)
          {
            global $calcValues;

            // echo "<ul>";
            $r=0;
            $lastItem='';

            // $yy=$totalizador+1;
            foreach($dRows as $kr => $vr) {
              if (is_array($vr)) {
                $r+=doCalcCol($calcCell, $evento, $tableName, $xx, $vr, $yy, $lastItem);
              } else {
                $ndx="row_$yy";
                $valores[$ndx]=$dRows[$kr];

                $cell="$calcCell($evento,$tableName,$xx,$yy,$totalizador)";
                $cell=analisarString($cell,$pegarDadosDaTabela, $nomeTabela, $campoChave, $valorChave, $valores);
                // echo "somando $xx,$yy ".$calcValues[$xx][$yy]."<br>";
                $r+=floatval($calcValues[$xx][$yy]);
                $lastItem=$yy;
                $yy++;
              }
            }
            $calcValues[$xx][$totalizador]+=floatval($r);

            // echo "sub-total ($r) em $totalizador: ".$calcValues[$xx][$totalizador]."</ul>";
            return $r;
          }

          function doCalcTable($calcCell, $evento, $tableName, $dCols, $dRows)
          {
            global $calcValues;

            $xx=0;
            foreach($dCols as $kc => $vc) {
              if (is_array($vc)) {
              } else {
                $yy=0;
                doCalcCol($calcCell, $evento, $tableName, $xx, $dRows, $yy);
                $xx++;
              }
            }
          }

?>
