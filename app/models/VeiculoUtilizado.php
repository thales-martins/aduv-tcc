<?php

class VeiculoUtilizado extends \HXPHP\System\Model
{
    static $table_name = 'veiculos_utilizados';

    public static function cadastrar(array $atributos)
    {
        self::create($atributos);
    }
}