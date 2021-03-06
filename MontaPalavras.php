<?php

/**
 * O método principal da classe é o "iniciaJogo", nele é realizada a chamada
 * dos demais métodos. Inicialmente, é carregado a partir de um arquivo json
 * (BancoPalavras.json) todas as palavras disponibilizadas para o jogo. Após
 * carregar as palavras, é iniciado uma estrutura de repetição, onde será 
 * realizado a leitura dos valores informados pelo usuário. Caso os valores
 * informados sejam válidos, é iniciado a montagem das palavras. Se for
 * informado o valor 0 (zero) na entrada referente as letras, a aplicação é 
 * finalizada.
 * 
 * Para montar as palavras com o conjunto de letras informados, é realizado um
 * loop percorrendo todas as palavras disponibilizadas. Para cada palavra, é
 * removido os acentos, por meio de substituição dos caracteres, e alterada para
 * a forma maiúscula. Ainda para cada palavra, é realizado um loop das letras
 * válidas, dentro desse loop, é verificado se a letra existe na palavra. Se a
 * letra existir na palavra, ela é removida dela, se não existir, a letra é 
 * colocada em uma lista de letras não utilizadas. O loop é executado até as 
 * letras da palavra acabarem ou as letras informadas acabarem. Se as letras da
 * palavra tiverem acabado, a palavra é adicionada na lista de palavras montadas,
 * junto com as letras que não foram utilizadas. Esse processo é realizado para
 * todas as palavras disponibilizadas. Infelizmente, não tive tempo para implementar
 * e testar adequadamente o "Ponto Extra" de "Multiplas palavras", mas acredito
 * que utilizando recursividade neste ponto, chamando o método "constroiPalavras"
 * dentro dele mesmo, passando as letras não usadas para montar aquela palavra e
 * ajustando os métodos de pontuação, seria possível alcançar o resultado.
 * 
 * Após montar as palavras, é verificado se alguma palavra foi realmente montada.
 * Caso existam palavras montadas, é iniciado a pontação daquelas palavras através
 * de um loop na lista de palavras montadas. Para cada palavra que foi montada,
 * as letras são divididas em um array, onde é aplicado o reduce para realizar
 * a soma dos pontos da palavra. Para cada letra da palavra, é retornado a sua
 * pontuação e verificado se está na posição bônus. Se estiver na posição bônus
 * a pontuação daquela letra é dobrado. Após realizar a soma dos pontos daquela
 * palavra, ela é adicionada em uma lista que já agrupa as palavras com a mesma
 * quantidade de pontos.
 * 
 * Pontuando e agrupando todas as palavras que foram montadas, é possível escolher
 * a palavra com a maior pontuação. A lista de palavras montadas é ordenada de
 * forma decrescente pelo índice, que é a pontuação daquelas palavras, e retornado
 * a primeira posição. Caso exista mais de uma palavra com aquela pontuação, é
 * realizado a comparação pelo tamanho daquelas palavras, onde é retornado uma
 * lista com as menores palavras. Caso seja mais de uma palavra, é verificado a
 * ordem alfabética daquelas palavras realizando uma ordenação do array pela
 * palavra e retornando a primeira posição.
 * 
 * Para finalizar, a melhor palavra é impressa na tela.
 */

class MontaPalavras
{
    private const MSG_TITULO = "Monta Palavras\n";
    private const MSG_ENTRADA_LETRAS = 'Digite as letras disponíveis nesta jogada: ';
    private const MSG_ENTRADA_POSICAO = 'Digite a posição bônus: ';
    private const MSG_LETRAS_INVALIDAS = "\nNão foi informado nenhuma letra válida.\nLetras válidas: De A até Z, maiúscula ou minúscula e sem acentos ou 'Ç'.\n";
    private const MSG_BONUS_INVALIDO = "\nPosição bônus deve ser um número inteiro maior ou igual a 1.\nPara desconsiderar o bônus, digite 0.\n";
    private const MSG_SEPARADOR = "\n============================================================\n";
    private const BANCO_PALAVRAS = 'BancoPalavras.json';

    /**
     * Diretório do arquivo onde está localizado o banco de palavras
     * @var string
     */
    private $dirBancoPalavras;

    public function __construct()
    {
        $this->dirBancoPalavras = dirname(__FILE__) . '/' . self::BANCO_PALAVRAS;
    }

    /**
     * Método principal da classe que inicia o jogo
     * 
     * Método responsável por preparar o ambiente para o jogo e realizar as 
     * chamadas dos principais métodos da classe.
     * 
     * @return void
     */
    public function iniciaJogo()
    {
        print_r(self::MSG_TITULO);
        
        try {
            $listaBancoPalavras = $this->retornaBancoPalavras();

            do {
                print_r(self::MSG_SEPARADOR);

                list($letrasValidas, $caracteresInvalidos, $posicaoBonus) = $this->retornaEntradas();

                if ($letrasValidas && is_int($posicaoBonus)) {
                    $melhorPalavra = [];

                    $listaPalavrasMontadas = $this->constroiPalavras($letrasValidas, $listaBancoPalavras);

                    if (!empty($listaPalavrasMontadas)) {
                        $listaPalavrasPontuadas = $this->pontuaPalavrasMontadas($listaPalavrasMontadas, $posicaoBonus);
                        $melhorPalavra = $this->retornaPalavraMelhorPontuacao($listaPalavrasPontuadas);
                    }

                    $this->imprimeResultado($melhorPalavra, $letrasValidas, $caracteresInvalidos);
                }
            } while ($letrasValidas !== '0');
        } catch (Throwable $t) {
            print_r($t->getMessage());
        }
    }

    /**
     * Verifica se um arquivo existe 
     * 
     * Verifica se o arquivo existe no diretório informado. Se o arquivo for
     * encontrado, é retornado o booleano true, caso não encontrado, é gerado
     * um erro
     * 
     * @param string $arquivo Diretório do arquivo a ser verificado
     * 
     * @return bool
     */
    private function verificaArquivoExiste($arquivo)
    {
        $arquivoExiste = file_exists($arquivo);

        if (!$arquivoExiste) {
            throw new Error("Não foi encontrado o arquivo");
        }

        return true;
    }

    /**
     * Verifica se o arquivo possui permissão para leitura
     * 
     * Verifica se o arquivo do diretório informado possui permissão para
     * leitura. Se for possível ler o arquivo, é retornado o booleano true,
     * caso não seja possível ler o arquivo, é gerado um erro
     * 
     * @param string $arquivo Diretório do arquivo a ser verificado
     * 
     * @return bool
     */
    private function verificaPermissaoLeitura($arquivo)
    {
        $permissaoLeitura = is_readable($arquivo);

        if (!$permissaoLeitura) {
            throw new Error("Não é possível ler o arquivo");
        }

        return true;
    }

    /**
     * Método que retorna apenas letras
     * 
     * Recebe uma string contendo qualquer caracter e retorna apenas as letras
     * maiúsculas ou minúsculas e que não possuam caracteres especiais. 
     * [a-zA-Z]
     * 
     * @param string $string Conjunto de caracteres
     * 
     * @return string
     */
    private function retornaLetras($string)
    {
        return preg_replace("/[^a-z]+/i", "", $string); 
    }

    /**
     * Método que retorna tudo que não for uma letra
     * 
     * Recebe uma string contendo qualquer caracter e retorna o que não
     * for letra e letras acentuadas
     * 
     * @param string $string Conjunto de caracteres
     * 
     * @return string
     */
    private function retornaCaracteresEspeciais($string)
    {
        return preg_replace("/[a-z ]+/i", "", $string);
    }

    /**
     * Método que transforma string em um array
     * 
     * Recebe qualquer valor e, caso seja uma string, retorna um array contendo
     * os caracteres daquela string separados em cada posição. Caso a entrada
     * seja um array, retorna o array sem modificações. Caso a entrada seja
     * outro tipo, retorna um array vazio.
     * 
     * @param mixed $valor Valor a ser convertido
     * 
     * @return array
     */
    private function retornaListaCaracteres($valor)
    {
        if (!is_array($valor)) {
            if (is_string($valor) && !empty($valor)) {
                $valor = mb_str_split($valor);
            } else {
                $valor = [];
            }
        }

        return $valor;
    }

    /**
     * Método responsável por retirar os acentos de uma palavra
     * 
     * @param string $palavra Palavra que terá os acentos retirados
     * 
     * @return string
     */
    private function removeAcentos($palavra)
    {
        $substituir = [
            'Ç' => 'C',
            'Á' => 'A',
            'À' => 'A',
            'Â' => 'A',
            'Ã' => 'A',
            'É' => 'E',
            'È' => 'E',
            'Ê' => 'E',
            'Í' => 'I',
            'Ì' => 'I',
            'Î' => 'I',
            'Ó' => 'O',
            'Ò' => 'O',
            'Ô' => 'O',
            'Õ' => 'O',
            'Ú' => 'U',
            'Ù' => 'U',
            'Û' => 'U'
        ];
        
        $palavraMaiuscula = mb_strtoupper($palavra);

        return strtr($palavraMaiuscula, $substituir);
    }

    /**
     * Método responsável por converter uma string para um inteiro
     * 
     * Verifica se a string informada é uma string numérica e, caso verdadeiro,
     * retorna a string numérica convertida em um número inteiro.
     * 
     * @param string $string Valor a ser convertido
     * 
     * @return int|bool
     */
    private function converteStringParaInteiro($string)
    {
        if (is_numeric($string)) {
            return (int)$string;
        }

        return false;
    }

    /**
     * Método responsável por centralizar a leitura do que for digitado
     * 
     * Realiza as chamadas dos métodos de entrada
     * 
     * @return array
     */
    private function retornaEntradas()
    {
        list($letrasValidas, $caracteresInvalidos) = $this->retornaLetrasDisponiveis();
        $posicaoBonus = false;

        if ($letrasValidas) {
            $posicaoBonus = $this->retornaPosicaoBonus();
        }

        return [$letrasValidas, $caracteresInvalidos, $posicaoBonus];
    }

    /**
     * Método responsável por ler as palavras digitadas
     * 
     * Recebe os valores digitados referentes as letras para formar as palavras.
     * Realiza as validações e tratativas necessárias e retorna as letras válidas
     * e os caracteres inválidos.
     * 
     * @return array
     */
    private function retornaLetrasDisponiveis()
    {
        $letrasDisponiveis = mb_strtoupper(readline(self::MSG_ENTRADA_LETRAS));
        $caracteresInvalidos = '';

        if ($letrasDisponiveis !== '0') {
            $letrasValidas = $this->retornaLetras($letrasDisponiveis);

            if ($letrasValidas) {
                $caracteresInvalidos = $this->retornaCaracteresEspeciais($letrasDisponiveis);
            } else {
                print_r(self::MSG_LETRAS_INVALIDAS);
            }
        } else {
            $letrasValidas = '0';
        }

        return [$letrasValidas, $caracteresInvalidos];
    }

    /**
     * Método responsável por ler a posição bônus
     * 
     * Recebe os valores digitados referentes a posição bônus. Realiza as
     * validações e tratativas necessárias e retorna a posição.
     * 
     * @return int|bool
     */
    private function retornaPosicaoBonus()
    {
        $posicaoBonus = readline(self::MSG_ENTRADA_POSICAO);

        $posicaoBonus = $this->converteStringParaInteiro($posicaoBonus);

        if ($posicaoBonus === false || $posicaoBonus < 0) {
            $posicaoBonus = false;
            print_r(self::MSG_BONUS_INVALIDO);
        }

        return $posicaoBonus;
    }

    /**
     * Método responável por retorna as palavras do banco de palavras
     * 
     * Verifica se o arquivo onde estão as palavras do banco de palavras existe
     * e se é possível realizar a leitura do mesmo, retornando um array com
     * todas as palavras disponíveis. Caso não exista ou não seja possível ler
     * o arquivo, é gerado um erro
     * 
     * @return array
     */
    private function retornaBancoPalavras()
    {
        try {
            $this->verificaArquivoExiste($this->dirBancoPalavras);
            $this->verificaPermissaoLeitura($this->dirBancoPalavras);

            $bancoPalavrasJson = file_get_contents($this->dirBancoPalavras);
            $bancoPalavras = json_decode($bancoPalavrasJson, true);

            return $bancoPalavras['palavras'];
        } catch (Throwable $t) {
            throw new Error($t->getMessage() . ' com o Banco de Palavras');
        }
    }

    /**
     * Método responsável por montar as palavras
     * 
     * Recebe as letras válidas informadas pelo usuário e a lista de palavras
     * do banco de palavras e constroi uma nova lista com todas as palavras 
     * formadas por aquelas letras. Junto com a palavra formada, também é
     * retornada as letras válidas que não foram utilizadas. Caso não seja
     * formada nenhuma palavra, é retornado uma lista vazia.
     * 
     * @param string $letrasValidas Letras válidas para formar as palavras
     * @param array $listaBancoPalavras Lista de todas as palavras disponibilizadas
     * 
     * @return array
     */
    private function constroiPalavras($letrasValidas, $listaBancoPalavras)
    {
        try {
            $listaLetras = $this->retornaListaCaracteres($letrasValidas);
            $listaPalavrasMontadas = [];

            foreach ($listaBancoPalavras as $palavra) {
                $palavra = $this->removeAcentos($palavra);
                $palavraAuxiliar = $palavra;
                $listaLetrasNaoUsadas = [];

                foreach ($listaLetras as $index=>$letra) {
                    if (!empty($palavraAuxiliar)) {
                        $posicaoLetra = strpos($palavraAuxiliar, $letra);

                        if ($posicaoLetra === false) {
                            $listaLetrasNaoUsadas[] = $letra;
                        } else {
                            $palavraAuxiliar = substr_replace($palavraAuxiliar, '', $posicaoLetra, 1);
                        }
                    } else {
                        $letrasNaoUsadas = array_slice($listaLetras, $index);
                        $listaLetrasNaoUsadas = array_merge($listaLetrasNaoUsadas, $letrasNaoUsadas);
                        break;
                    }
                }

                if (empty($palavraAuxiliar)) {
                    $palavraConstruida['palavra'] = $palavra;
                    $palavraConstruida['letrasNaoUsadas'] = $listaLetrasNaoUsadas;
                    
                    $listaPalavrasMontadas[] = $palavraConstruida;
                }
            }

            return $listaPalavrasMontadas;
        } catch (Throwable $t) {
            throw new Error('Erro ao construir as palavras');
        }
    }

    /**
     * Método responsável por pontuar as palavras formadas
     * 
     * Recebe uma lista com as palavras montadas e realiza a pontuação de cada
     * palavra de acordo com os valores de cada letra. Caso o parâmetro bonus
     * seja maior que zero, o ponto da letra naquela posição será duplicado.
     * Retorna uma nova lista com as palavras pontuadas e agrupadas pelo número
     * de pontos.
     * 
     * @param array $listaPalavrasMontadas Lista com todas as palavras montadas
     * @param int $posicaoBonus Posição bonus que duplicará valor da letra
     * 
     * @return array
     */
    private function pontuaPalavrasMontadas($listaPalavrasMontadas, $posicaoBonus)
    {
        try {
            $listaPalavrasPontuadas = [];

            foreach ($listaPalavrasMontadas as $palavrasMontadas) {
                $listaLetrasPalavra = $this->retornaListaCaracteres($palavrasMontadas['palavra']);
                $bonusAuxiliar = $posicaoBonus;

                $totalPontos = array_reduce($listaLetrasPalavra, function($pontos, $letra) use (&$bonusAuxiliar) {
                    $pontoLetra = $this->retornaPontoLetra($letra);

                    if ($bonusAuxiliar === 1) {
                        $pontoLetra = $pontoLetra * 2;
                    }

                    $pontos += $pontoLetra;
                    $bonusAuxiliar--;

                    return $pontos;
                }, 0);
                
                $palavrasMontadas['pontos'] = $totalPontos;
                $listaPalavrasPontuadas[$totalPontos][] = $palavrasMontadas;
            }
            
            return $listaPalavrasPontuadas;
        } catch (Throwable $t) {
            throw new Error('Erro ao pontuar as palavras');
        }
    }

    /**
     * Método responsável por retornar o ponto de cada letra
     * 
     * @param string $letra Letra que será pontuada
     * 
     * @return int
     */
    private function retornaPontoLetra($letra)
    {
        $pontos = [
            "A" => 1,
            "B" => 3,
            "C" => 3,
            "D" => 2,
            "E" => 1,
            "F" => 5,
            "G" => 2,
            "H" => 5,
            "I" => 1,
            "J" => 8,
            "L" => 1,
            "M" => 3,
            "N" => 1,
            "O" => 1,
            "P" => 3,
            "Q" => 13,
            "R" => 1,
            "S" => 1,
            "T" => 1,
            "U" => 1,
            "V" => 5,
            "X" => 8,
            "Z" => 13
        ];

        return $pontos[$letra];
    }

    /**
     * Método responsável por retornar a palavra com melhor pontuação
     * 
     * Recebe a lista com as palavras pontuadas e agrupadas pelos pontos.
     * O primeiro critério para escolher a palavra com melhor pontuação é 
     * a maior quantidade de pontos daquela palavra. Caso exista mais de uma
     * palavra com o maior número de pontos, é levado em consideração o tamanho
     * daquelas palavras. Caso as palavras tenham o mesmo tamanho, é levado em
     * consideração a ordem alfabética das palavras.
     * 
     * @param array $listaPalavrasPontuadas Lista com as palavras pontuadas
     * 
     * @return array
     */
    private function retornaPalavraMelhorPontuacao($listaPalavrasPontuadas)
    {
        try {
            $palavrasMelhorPontuacao = $this->comparaPontos($listaPalavrasPontuadas);

            if (count($palavrasMelhorPontuacao) > 1) {
                $palavrasMelhorPontuacao = $this->comparaTamanho($palavrasMelhorPontuacao);

                if (count($palavrasMelhorPontuacao) > 1) {
                    $this->comparaOrdemAlfabetica($palavrasMelhorPontuacao);
                }
            }

            $palavra = array_shift($palavrasMelhorPontuacao);
            
            return $palavra;
        } catch (Throwable $t) {
            throw new Error('Erro ao escolher a melhor palavra');
        }
    }

    /**
     * Método responsável por retornar as palavras com maior pontuação
     * 
     * Recebe a lista de palavras pontuadas e agrupadas pelos pontos, ordena de
     * forma decrescente e retorna apenas as palavras com a maior pontuação
     * 
     * @param array $listaPalavrasPontuadas Lista com as palavras pontuadas
     * 
     * @return array
     */
    private function comparaPontos($listaPalavrasPontuadas)
    {
        krsort($listaPalavrasPontuadas);
        $palavrasMelhorPontuacao = array_shift($listaPalavrasPontuadas);

        return $palavrasMelhorPontuacao;
    }

    /**
     * Método responsável por retornar apenas as menores palavras
     * 
     * Recebe a lista de palavras com a maior pontuação e retorna apenas 
     * aquelas palavras com o menor tamanho
     * 
     * @param array $listaPalavras Lista de palavras com maior pontuação
     * 
     * @return array
     */
    private function comparaTamanho($listaPalavras)
    {
        $primeiraPalavra = array_shift($listaPalavras);
        $menorTamanho = strlen($primeiraPalavra['palavra']);
        $listaMenorPalavra[] = $primeiraPalavra;
        
        foreach ($listaPalavras as $palavra) {
            $tamanhoPalavraAtual = strlen($palavra['palavra']);
        
            if ($tamanhoPalavraAtual <= $menorTamanho) {
                if ($tamanhoPalavraAtual < $menorTamanho) {
                    $listaMenorPalavra = [];
                    $menorTamanho = $tamanhoPalavraAtual;
                }
        
                $listaMenorPalavra[] = $palavra;
            }
        }

        return $listaMenorPalavra;
    }

    /**
     * Método responsável por ordenar as palavras em ordem alfabética
     * 
     * Recebe a lista com as menores palavras com maior pontuação e realiza a
     * ordeção de forma alfabética
     * 
     * @param array $listaPalavras Lista das menores palavras com maior pontuação
     * 
     * @return void
     */
    private function comparaOrdemAlfabetica(&$listaPalavras)
    {
        usort($listaPalavras, function($a, $b) {
            return strnatcmp($a['palavra'], $b['palavra']);
        });
    }

    /**
     * Método responsável por modelar e imprimir o resultado
     * 
     * Recebe a melhor palavra escolhida e as letras informadas, organiza os dados
     * e imprime no terminal em uma forma mais legível
     * 
     * @param array $melhorPalavra A melhor palavra montada
     * @param string $letrasValidas Letras válidas informadas pelo usuário
     * @param string $caracteresInvalidos Caracteres não válidos informados pelo usuário
     * 
     * @return void
     */
    private function imprimeResultado($melhorPalavra, $letrasValidas, $caracteresInvalidos)
    {
        if (!empty($melhorPalavra)) {
            $palavra = $melhorPalavra['palavra'];
            $pontos = $melhorPalavra['pontos'];
            $listaLetrasNaoUsadas = $melhorPalavra['letrasNaoUsadas'];

            $mensagem = "\n$palavra, palavra de $pontos pontos";

            $letraNaoUsadas = $this->constroiLetrasNaoUsadas($listaLetrasNaoUsadas, $caracteresInvalidos);
        } else {
            $mensagem = "\nNenhuma palavra encontrada";
            $letraNaoUsadas = $this->constroiLetrasNaoUsadas($letrasValidas, $caracteresInvalidos);
        }

        print_r($mensagem);

        if ($letraNaoUsadas) {
            print_r("\nSobraram: $letraNaoUsadas");
        }

        print_r("\n");
    }

    /**
     * Método responsável por tratar as letras não utilizadas na montagem da melhor palavra
     * 
     * @param string|array $letrasNaoUsadas Pode ser uma string ou uma lista das letras não utilizadas
     * @param string $caracteresInvalidos Caracteres não válidos informados pelo usuário
     * 
     * @return string
     */
    private function constroiLetrasNaoUsadas($letrasNaoUsadas, $caracteresInvalidos)
    {
        $naoUsadas = $this->retornaListaCaracteres($letrasNaoUsadas);
        $invalidos = $this->retornaListaCaracteres($caracteresInvalidos);

        $listaLetrasNaoUsadas = array_merge($naoUsadas, $invalidos);

        return implode(', ', $listaLetrasNaoUsadas);
    }
}
