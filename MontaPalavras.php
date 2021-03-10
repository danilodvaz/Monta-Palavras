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

    private function retornaBancoPalavras()
    {
        $bancoPalavrasJson = file_get_contents('./BancoPalavras.json');
        $bancoPalavras = json_decode($bancoPalavrasJson, true);

        return $bancoPalavras['palavras'];
    }

    private function constroiPalavras($letrasValidas)
    {
        $palavras = $this->retornaBancoPalavras();
        $listaLetras = str_split($letrasValidas);

        foreach ($palavras as $palavra) {
            foreach ($listaLetras as $letra) {
                if (!empty($palavra)) {
                    $posicaoLetra = strpos($palavra, $letra);

                    if ($posicaoLetra === false) {
                        // Letra não utilizada para formar a palavra
                    } else {
                        $palavra = substr_replace($palavra, '', $posicaoLetra, 1);
                    }
                } else {
                    break;
                }
            }

            if (empty($palavra)) {
                // adiciona a palavra na lista de montadas
                // adiciona as letras não utilizadas na lista de letras não utilizadas.
            }
        }

    }
}
