<?php

class Mandado extends \HXPHP\System\Model
{
    static $table_name = 'mandados';

    static $validates_presence_of = array(
        array(
            'descricao',
            'message' => 'A descrição é um campo obrigatório.'
        ),
        array(
            'numero_protocolo',
            'message' => 'O número de protocolo é um campo obrigatório.'
        ),
        array(
            'id_interessado',
            'message' => 'O interessado é um campo obrigatório.'
        ),
        array(
            'id_promotoria',
            'message' => 'A promotoria é um campo obrigatório.'
        )
    );

    public static function cadastrar(array $post)
    {
        $resposta = new \stdClass;
        $resposta->mandado = null;
        $resposta->status = false;
        $resposta->errors = array();

        $mandado = self::create($post);

        if($mandado->is_valid()) {
            $resposta->mandado = $mandado;
            $resposta->status = true;
            return $resposta;
        }

        $errors = $mandado->errors->get_raw_errors();

        foreach ($errors as $key => $message) {
            array_push($resposta->errors, $message[0]);
        }

        return $resposta;
    }
}