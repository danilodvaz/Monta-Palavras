<?php

class MontaPalavras
{
    private const MSG_ENTRADA = 'Digite as letras disponíveis nesta jogada: ';
    private const MSG_SEPARADOR = "============================================================\n";

    public function iniciaJogo()
    {
        print_r("Monta Palavras\n\n");

        do {
            print_r(self::MSG_SEPARADOR);

            $entrada = readline(self::MSG_ENTRADA);
            
            if ($entrada != 'exit') {
                $letrasValidas = $this->retornaLetras($entrada);
                $caracteresInvalidos = $this->retornaCaracteresEspeciais($entrada);

                $listaPalavrasMontadas = $this->constroiPalavras($letrasValidas);
                $listaPalavrasPontuadas = $this->pontuaPalavrasMontadas($listaPalavrasMontadas);
                $melhorPalavra = $this->retornaPalavraMelhorPontuacao($listaPalavrasPontuadas);

                $this->imprimeResultado($melhorPalavra, $caracteresInvalidos);
            }
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

    private function retornaListaLetras($string)
    {
        return str_split(mb_strtoupper($string));
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
        $listaLetras = $this->retornaListaLetras($letrasValidas);
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
            $listaLetrasPalavra = str_split($palavrasMontadas['palavra']);
            
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
            "E" => 1,
            "A" => 1,
            "I" => 1,
            "O" => 1,
            "N" => 1,
            "R" => 1,
            "T" => 1,
            "L" => 1,
            "S" => 1,
            "U" => 1,
            "D" => 2,
            "G" => 2,
            "B" => 3,
            "C" => 3,
            "M" => 3,
            "P" => 3,
            "F" => 5,
            "H" => 5,
            "V" => 5,
            "J" => 8,
            "X" => 8,
            "Q" => 13,
            "Z" => 13
        ];

        return $pontos[$letra];
    }

    private function retornaPalavraMelhorPontuacao($listaPalavrasPontuadas)
    {
        $palavrasMelhorPontuacao = $this->comparaPontos($listaPalavrasPontuadas);

        if (count($palavrasMelhorPontuacao) > 1) {
            $this->comparaOrdemAlfabetica($palavrasMelhorPontuacao);
            $this->comparaTamanho($palavrasMelhorPontuacao);
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

    private function comparaOrdemAlfabetica(&$listaPalavras)
    {
        function desempataOrdem($a, $b)
        {
            return strnatcmp($a['palavra'], $b['palavra']);
        }

        usort($listaPalavras, 'desempataOrdem');
    }

    private function comparaTamanho(&$listaPalavras)
    {
        function desempataTamanho($a, $b)
        {
            return strlen($a['palavra']) <=> strlen($b['palavra']);
        }

        usort($listaPalavras, 'desempataTamanho');
    }

    private function imprimeResultado($melhorPalavra, $caracteresInvalidos)
    {
        $palavra = $melhorPalavra['palavra'];
        $pontos = $melhorPalavra['pontos'];
        $listaLetrasNaoUsadas = $melhorPalavra['letrasNaoUsadas'];

        $letraNaoUsadas = $this->constroiLetrasNaoUsadas($listaLetrasNaoUsadas, $caracteresInvalidos);

        print_r("\n$palavra, palavra de $pontos pontos");

        if ($letraNaoUsadas) {
            print_r("\nSobraram: $letraNaoUsadas");
        }

        print_r("\n\n");
    }

    private function constroiLetrasNaoUsadas($listaLetrasNaoUsadas, $caracteresInvalidos)
    {
        $letraNaoUsadas = '';

        if (!empty($listaLetrasNaoUsadas)) {
            $letraNaoUsadas = implode(', ', $listaLetrasNaoUsadas);
        }

        if (!empty($caracteresInvalidos)) {
            $listaCaracteresInvalidos = str_split($caracteresInvalidos);
            $letraNaoUsadas += ', ' + implode(', ', $listaCaracteresInvalidos);
        }

        return $letraNaoUsadas;
    }
}
