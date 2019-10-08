<?php

# Programacion escalable con call backs

function recogerBasura( $callbackFormaDeRecoger )
{
  $basura = '%#$@'; // buscar la basura

  $basuraEncapsulada = '('.$basura.')';

  $resultado = $callbackFormaDeRecoger($basuraEncapsulada);

  return (gettype($resultado)==='string'
          ? $resultado
          : throw new Exception("Error Processing Request", 1)  
        );

}

$resultado = recogerBasura(function($basuraEncapsulada_){
  $basuraRecogida = $basuraEncapsulada_.' con pala';

  return $basuraRecogida;
});

/**************************************************************************/


      $final = [
        [
          'field_name' => 'region_id',
          'operator'   => '=',
          'value'      => '56'
        ],
        [
          'field_name' => 'property_type',
          'operator'   => 'ILKE',
          'value'      => 'otro',
          'validar_con_modelo' => function()
          {
            $type_name = PropertyType::find($property_type)->name;
            return 0;
          }
        ]
      ];

      $value = 'region_id';
      construir_$value();
      __call() // <- buscar para validar si una funcion existe

      // 1 procesar parametros
      // 2 construir dicc
      // 3 construir el query
      // 4 ejecutar query

