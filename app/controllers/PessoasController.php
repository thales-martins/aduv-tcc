<?php

class PessoasController extends \HXPHP\System\Controller
{
    public function __construct(\HXPHP\System\Configs\Config $configs = null)
    {
        parent::__construct($configs);

        $this->load(
            'Services\Auth',
            $configs->auth->after_login,
            $configs->auth->after_logout,
            true
        );

        $this->auth->redirectCheck(false);
    }

    public function indexAction()
    {
        $this->auth->roleCheck(array('C'));

        $this->view->setPath('blank', true)
            ->setFile('index')
            ->setTemplate(false);
    }

    public function cadastrarAction()
    {
        $this->auth->roleCheck(array('C'));

        $this->view->setPath('blank', true)
            ->setFile('index')
            ->setTemplate(false);

        $post = $this->request->post();

        if(!empty($post)) {
            $this->load('Services\DateConverter');

            $pessoa = array(
                'nome' => $post['nome'],
                'cpf' => $post['cpf'],
                'data_nascimento' => $this->dateconverter->toMySqlFormat($post['dataNascimento'])
            );

            $resposta = Pessoa::cadastrar($pessoa);

            if($resposta->status) {
                $pessoa = $resposta->pessoa;

                $telefone = array(
                    'ddd' => $post['ddd'],
                    'numero' => $post['numeroTelefone'],
                    'id_pessoa' => $pessoa->id
                );

                $resposta = Telefone::cadastrar($telefone);

                if($resposta->status) {
                    $telefone = $resposta->telefone;

                    $endereco = array(
                        'logradouro' => $post['logradouro'],
                        'numero' => $post['numeroEndereco'],
                        'complemento' => $post['complemento'],
                        'cep' => $post['cep'],
                        'bairro' => $post['bairro'],
                        'id_cidade' => ($post['cidade'] == 0) ? null : $post['cidade'],
                        'id_pessoa' => $pessoa->id,
                    );

                    $resposta = Endereco::cadastrar($endereco);

                    if($resposta->status) {
                        $resposta = array(
                            'pessoa' => array(
                                'id' => $pessoa->id,
                                'nome' => $pessoa->nome
                            ),
                            'status' => $resposta->status,
                            'errors' => $resposta->errors
                        );

                        echo json_encode($resposta);
                    } else {
                        $pessoa->delete();
                        $telefone->delete();

                        echo json_encode($resposta);
                    }
                } else {
                    $pessoa->delete();

                    echo json_encode($resposta);
                }
            } else {
                echo json_encode($resposta);
            }
        }
    }

    public function getPessoasAction()
    {
        $this->auth->roleCheck(array('C'));

        $this->view->setPath('blank', true)
            ->setFile('index')
            ->setTemplate(false);

        $nome = $this->request->post('nome');

        $pessoas = Pessoa::all(array(
            'conditions' => "nome like '%$nome%'"
        ));

        $resposta = array();

        foreach ($pessoas as $pessoa) {
            $resposta[] = array(
                'id' => $pessoa->id,
                'nome' => $pessoa->nome,
                'cpf' => $pessoa->cpf
            );
        }

        echo json_encode($resposta);
    }

    public function getPessoaAction($id = null)
    {
        $this->auth->roleCheck(array('C', 'O'));

        $this->view->setPath('blank', true)
            ->setFile('index')
            ->setTemplate(false);

        $resposta = array();

        if(!empty(filter_var($id, FILTER_VALIDATE_INT))) {
            $pessoa = Pessoa::find_by_id($id);

            if(!empty($pessoa)) {
                $telefone = Telefone::find_by_id_pessoa($pessoa->id);
                $endereco = Endereco::find_by_id_pessoa($pessoa->id);

                if(!empty($endereco)) {
                    $cidade = Cidade::find_by_id($endereco->id_cidade);
                    $uf = Uf::find_by_id($cidade->id_uf);
                }

                $resposta = array(
                    'nome' => $pessoa->nome,
                    'cpf' => $pessoa->cpf,
                    'dataNascimento' => (!empty($pessoa->data_nascimento)) ? $pessoa->data_nascimento->format('d/m/Y') : '',
                    'telefone' => (!empty($telefone)) ? "($telefone->ddd) $telefone->numero" : '',
                    'endereco' => (!empty($endereco)) ? "$endereco->logradouro, $endereco->numero, $endereco->complemento, $endereco->bairro
                                   <br/>
                                   $cidade->nome/$uf->uf
                                   <br/>
                                   $endereco->cep" : ''
                );
            }
        }

        echo json_encode($resposta);
    }
}