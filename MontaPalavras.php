<?php

class MontaPalavras
{
    private const MSG_ENTRADA = 'Digite as letras disponíveis nesta jogada: ';

    public function iniciaJogo()
    {
        print_r("Monta Palavras\n\n");

        do {
            $entrada = readline(self::MSG_ENTRADA);
            
            $letrasValidas = $this->retornaLetras($entrada);
            $letrasNaoUsadas = $this->retornaCaracteresEspeciais($entrada);

            $listaPalavrasMontadas = $this->constroiPalavras($letrasValidas);
            $listaPalavrasPontuadas = $this->pontuaPalavrasMontadas($listaPalavrasMontadas);
            $this->retornaPalavraMelhorPontuacao($listaPalavrasPontuadas);
            // var_dump('$letrasValidas', $letrasValidas);
            // var_dump('$letrasNaoUsadas', $letrasNaoUsadas);


        } while ($entrada != 'exit');
    }

    private function retornaLetras($string)
    {
        return preg_replace("/[^a-z]+/i", "", $string); 
    }

    private function retornaCaracteresEspeciais($string)
    {
        return preg_replace("/[a-z ]+/i", "", $string); 
    }

    private function removeAcentos($palavra)
    {
        $substituir = [
            'ç' => 'c',
            'á' => 'a',
            'à' => 'a',
            'â' => 'a',
            'ã' => 'a',
            'é' => 'e',
            'è' => 'e',
            'ê' => 'e',
            'í' => 'i',
            'ì' => 'i',
            'î' => 'i',
            'ó' => 'o',
            'ò' => 'o',
            'ô' => 'o',
            'õ' => 'o',
            'ú' => 'u',
            'ù' => 'u',
            'û' => 'u'
        ];
        
        $palavraMinuscula = mb_strtolower($palavra);

        return strtr($palavraMinuscula, $substituir);
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
        $listaLetras = str_split($letrasValidas);
        $listaPalavrasMontadas = [];

        foreach ($listaBancoPalavras as $palavra) {
            $palavraAuxiliar = $this->removeAcentos($palavra);
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
            $listaLetras = str_split($palavrasMontadas['palavra']);
            
            $totalPontos = array_reduce($listaLetras, function($pontos, $letra) {
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
            "e" => 1,
            "a" => 1,
            "i" => 1,
            "o" => 1,
            "n" => 1,
            "r" => 1,
            "t" => 1,
            "l" => 1,
            "s" => 1,
            "u" => 1,
            "d" => 2,
            "g" => 2,
            "b" => 3,
            "c" => 3,
            "m" => 3,
            "p" => 3,
            "f" => 5,
            "h" => 5,
            "v" => 5,
            "j" => 8,
            "x" => 8,
            "q" => 13,
            "z" => 13
        ];

        $letraMinuscula = mb_strtolower($letra);

        return $pontos[$letraMinuscula];
    }

    private function retornaPalavraMelhorPontuacao($listaPalavrasPontuadas)
    {
        krsort($listaPalavrasPontuadas);
        $palavrasMelhorPontuacao = array_shift($listaPalavrasPontuadas);
       
        if (count($palavrasMelhorPontuacao) > 1) {
            
        }

        die;
    }
}
