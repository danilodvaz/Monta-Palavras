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

            $this->constroiPalavras($letrasValidas);
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
                $palavraConstruida['palavraMontada'] = $palavra;
                $palavraConstruida['letrasNaoUsadas'] = $listaLetrasNaoUsadas;
                
                $listaPalavrasMontadas[] = $palavraConstruida;
            }
        }

        var_dump($listaPalavrasMontadas);
    }
}
