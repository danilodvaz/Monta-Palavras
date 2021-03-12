<?php

class MontaPalavras
{
    private const MSG_TITULO = "Monta Palavras\n\n";
    private const MSG_ENTRADA = 'Digite as letras disponíveis nesta jogada: ';
    private const MSG_SEPARADOR = "============================================================\n";

    public function iniciaJogo()
    {
        print_r(self::MSG_TITULO);

        do {
            print_r(self::MSG_SEPARADOR);

            $entrada = mb_strtoupper(readline(self::MSG_ENTRADA));
            $letrasValidas = $this->retornaLetras($entrada);

            if ($entrada != '0' && $letrasValidas) {
                $melhorPalavra = '';
                $caracteresInvalidos = $this->retornaCaracteresEspeciais($entrada);

                $listaPalavrasMontadas = $this->constroiPalavras($letrasValidas);

                if (!empty($listaPalavrasMontadas)) {
                    $listaPalavrasPontuadas = $this->pontuaPalavrasMontadas($listaPalavrasMontadas);
                    $melhorPalavra = $this->retornaPalavraMelhorPontuacao($listaPalavrasPontuadas);
                }

                $this->imprimeResultado($melhorPalavra, $letrasValidas, $caracteresInvalidos);
            }
        } while ($entrada != '0');
    }

    private function retornaLetras($string)
    {
        return preg_replace("/[^a-z]+/i", "", $string); 
    }

    private function retornaCaracteresEspeciais($string)
    {
        return preg_replace("/[a-z ]+/i", "", $string); 
    }

    private function retornaListaCaracteres($valor)
    {
        if (!is_array($valor)) {
            if (is_string($valor)) {
                $valor = str_split($valor);
            } else {
                $valor = [];
            }
        }

        return $valor;
    }

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

    private function retornaBancoPalavras()
    {
        $bancoPalavrasJson = file_get_contents('./BancoPalavras.json');
        $bancoPalavras = json_decode($bancoPalavrasJson, true);

        return $bancoPalavras['palavras'];
    }

    private function constroiPalavras($letrasValidas)
    {
        $listaBancoPalavras = $this->retornaBancoPalavras();
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
    }

    private function pontuaPalavrasMontadas($listaPalavrasMontadas)
    {
        $listaPalavrasPontuadas = [];

        foreach ($listaPalavrasMontadas as $palavrasMontadas) {
            $listaLetrasPalavra = $this->retornaListaCaracteres($palavrasMontadas['palavra']);
            
            $totalPontos = array_reduce($listaLetrasPalavra, function($pontos, $letra) {
                $pontoLetra = $this->retornaPontoLetra($letra);
                $pontos += $pontoLetra;

                return $pontos;
            }, 0);
            
            $palavrasMontadas['pontos'] = $totalPontos;

            $listaPalavrasPontuadas[$totalPontos][] = $palavrasMontadas;
        }
        
        return $listaPalavrasPontuadas;
    }

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

    private function retornaPalavraMelhorPontuacao($listaPalavrasPontuadas)
    {
        $palavrasMelhorPontuacao = $this->comparaPontos($listaPalavrasPontuadas);

        if (count($palavrasMelhorPontuacao) > 1) {
            $palavrasMelhorPontuacao = $this->comparaTamanho($palavrasMelhorPontuacao);

            if (count($palavrasMelhorPontuacao) > 1) {
                $this->comparaOrdemAlfabetica($palavrasMelhorPontuacao);
            }
        }

        $palavra = array_shift($palavrasMelhorPontuacao);
        
        return $palavra;
    }

    private function comparaPontos($listaPalavrasPontuadas)
    {
        krsort($listaPalavrasPontuadas);
        $palavrasMelhorPontuacao = array_shift($listaPalavrasPontuadas);

        return $palavrasMelhorPontuacao;
    }

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

    private function comparaOrdemAlfabetica(&$listaPalavras)
    {
        function desempataOrdem($a, $b)
        {
            return strnatcmp($a['palavra'], $b['palavra']);
        }

        usort($listaPalavras, 'desempataOrdem');
    }

    private function imprimeResultado($melhorPalavra, $letrasValidas, $caracteresInvalidos)
    {
        if (!empty($melhorPalavra)) {
            $palavra = $melhorPalavra['palavra'];
            $pontos = $melhorPalavra['pontos'];
            $listaLetrasNaoUsadas = $melhorPalavra['letrasNaoUsadas'];

            $mensagem = "\n$palavra, palavra de $pontos pontos";

            $letraNaoUsadas = $this->constroiLetrasNaoUsadas($listaLetrasNaoUsadas, $caracteresInvalidos);
        } else {
            $mensagem = "Nenhuma palavra encontrada";
            $letraNaoUsadas = $this->constroiLetrasNaoUsadas($letrasValidas, $caracteresInvalidos);
        }

        print_r($mensagem);

        if ($letraNaoUsadas) {
            print_r("\nSobraram: $letraNaoUsadas");
        }

        print_r("\n\n");
    }

    private function constroiLetrasNaoUsadas($letrasNaoUsadas, $caracteresInvalidos)
    {
        $naoUsadas = $this->retornaListaCaracteres($letrasNaoUsadas);
        $invalidos = $this->retornaListaCaracteres($caracteresInvalidos);

        $listaLetrasNaoUsadas = array_merge($naoUsadas, $invalidos);

        return implode(', ', $listaLetrasNaoUsadas);
    }
}
