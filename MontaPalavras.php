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

            
            var_dump('$letrasValidas', $letrasValidas);
            var_dump('$letrasNaoUsadas', $letrasNaoUsadas);


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
}
