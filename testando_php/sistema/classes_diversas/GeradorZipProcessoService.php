<?php

namespace App\Services\Processo;
use Exception;
use App\Models\Processo;
use App\Models\Advogado;
use Carbon\Carbon;
use ZipArchive;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpWord\TemplateProcessor;
use Barryvdh\DomPDF\Facade\Pdf;
use League\Flysystem\CalculateChecksumFromStream;

class GeradorZipProcessoService
{
    protected Processo $processo;
    protected string $recibo;

    public function __construct(Processo $processo, $recibo)
    {  
        $this->processo = $processo;
         $this->recibo = $recibo;    
    }

    public function executar(): string|array
    {
        $disk = Storage::disk('local');
        $disk->makeDirectory('temp');

        try {

                $mensagem = null;
                $wordPath = null;
                    

               
                // $wordPath = $this->gerarWord();
                try{              
                 if ($this->recibo === 'Sim') {
                   
                        $pdfPath = $this->gerarReciboPdf();
                        // dd($pdfPath);
                    } else {         
                        $pdfPath = null; // não gera PDF
                    }
                //    dd($this->processo);

                $conprocPdf = $this->gerarConprocPdf();
                 
                $wordPath = $this->gerarWord();
                
                }catch(\Throwable $e){
                $mensagem = $e->getMessage();
                }
                
                $excelPath = $this->gerarExcel();
                 
                $zipName = "processo_{$this->processo->processo}-id-{$this->processo->id}.zip";
                $zipPath = $disk->path("temp/$zipName");

                if ($disk->exists("temp/$zipName")) {
                    $disk->delete("temp/$zipName");
                }

                $zip = new ZipArchive();
                if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
                throw new \Exception('Erro ao criar ZIP');
                }
                


                if($conprocPdf){
                    $zip->addFile($conprocPdf, basename($conprocPdf));
                }

                if($pdfPath){
                    $zip->addFile($pdfPath, basename($pdfPath));
                }

                if($wordPath){                    
                    $zip->addFile($wordPath, basename($wordPath));
                }
                    
                $zip->addFile($excelPath, basename($excelPath));
                $zip->close();

                if ($conprocPdf && file_exists($conprocPdf)) {
                    unlink($conprocPdf);
                }
                if ($wordPath && file_exists($wordPath)) {
                    unlink($wordPath);
                }
                if ($pdfPath && file_exists($pdfPath)) {
                    unlink($pdfPath);
                }

                if (file_exists($excelPath)) {
                    unlink($excelPath);
                }
                                    
                // if($wordPath){     
                //     @unlink($wordPath);
                // }
                // @unlink($excelPath);
                    // dd($zipPath);
                 
                return [  "zipPath" => $zipPath,
                        "msg"  =>$mensagem
                        ];

                 // return $zipPath;
        } catch (\Throwable $e) {
            Log::error('Erro ao gerar ZIP processo', [
                'processo_id' => $this->processo->id,
                'erro' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /* WORD */
    private function gerarWord(): string
    {  
       
        $disk = Storage::disk('local');

        $arquivo = $this->processo->advogado->arquivo_docx_path;
        
        
        if (empty($arquivo)) {
            throw new Exception("Advogado não possui template DOCX cadastrado no Banco.");
            
        }


        $caminho = resource_path('templates/word/' . $arquivo);

       
        if (!file_exists($caminho)) {
        
            throw new \Exception("Template '{$arquivo}' não encontrado no servidor.");
        }

         
    //  $caminho = 'templates/word/berkmans.docx';
        $template = new TemplateProcessor($caminho);

        // $template = new TemplateProcessor(resource_path($caminho));
        
        // $advogado = Advogado::find($this->processo->advogado_id);
            // dd($this->processo->processoReclamada);
        $template->setValue('vara', $this->processo->vara);               
        $template->setValue('municipio', $this->processo->municipio);     
        $template->setValue('processo', $this->processo->processo);       
        $template->setValue('reclamante', $this->processo->reclamante);   
        $template->setValue('reclamada', $this->processo->processoReclamada?->nome?? $this->processo->reclamada_avulsa); 
            // tags para ser inserido no arquivo word
            // ${vara}ª
            // ${municipio}
            //  ${processo}
            //  ${reclamante}
            // ${reclamada}
        
        
        $nomeCompleto = $this->retornarNomeArquivo("docx",null);
          
        // $file = "processo_{$this->processo->processo}-{$this->processo->fazer}-".now()->format('Y-m-d').".docx";
        $file = $nomeCompleto;
        $path = $disk->path("temp/$file");

        $template->saveAs($path);
        
        return $path;
        // return "joao";
    }

    /* EXCEL */
    private function gerarExcel(): string
    {
        $disk = Storage::disk('local');
        try{
            $spreadsheet = IOFactory::createReader('Xlsx')
                ->load(resource_path('templates/excel/modelo_processo.xlsx'));        

        }catch(\Throwable $e){
             throw new \Exception("Atençao!!! arquivo Localizado em: templates/excel/modelo_processo.xlsx  nao encontrado.");
        }
       


        // Copia Históricos Salariais
        $this->copiarValoresDoModelo(
            $spreadsheet->getSheetByName('Históricos Salariais modelo'),
            $spreadsheet->getSheetByName('Históricos Salariais'),
            $this->retornarDataInicio('Históricos Salariais'),
            $this->processo->normalizarDataDemissaoMesAno(),
            true,
            'AU',
            'A',
            "Q194:Y209",
            'W'
        );

        // Copia Controles de Frequência
        $this->copiarValoresDoModelo(
            $spreadsheet->getSheetByName('Controles de Frequência modelo'),
            $spreadsheet->getSheetByName('Controles de Frequência'),
            $this->retornarDataInicio('Controles de Frequência'),
            Carbon::parse($this->processo->demissao),
            false,
            'BA',
            'A',
            "AQ5847:AT6038",
            'AQ'
        );

        // Remove abas modelo
        foreach (['Históricos Salariais modelo', 'Controles de Frequência modelo'] as $aba) {
            if ($sheet = $spreadsheet->getSheetByName($aba)) {
                $spreadsheet->removeSheetByIndex($spreadsheet->getIndex($sheet));
            }
        }

            //         // Pega a aba que será aberta por padrão
            // $sheetDestino = $spreadsheet->getSheetByName('Históricos Salariais');

            // // Define a aba como ativa
            // $spreadsheet->setActiveSheetIndex(
            //     $spreadsheet->getIndex($sheetDestino)
            // );

            // // Cria uma nova SheetView (reseta seleção e rolagem)
            // $view = new SheetView();
            // $view->setActiveCell('A1');       // garante célula ativa
            // $view->setTopLeftCell('A1');      // garante que a rolagem fique no topo
            // $sheetDestino->setSheetView($view);



                    //Define aba "Históricos Salariais" como ativa
        $sheetDestino = $spreadsheet->getSheetByName('Históricos Salariais');
        $spreadsheet->setActiveSheetIndex($spreadsheet->getIndex($sheetDestino));
        $sheetDestino->setSelectedCell('A1');
        $nomeCompleto = $this->retornarNomeArquivo("xlsx","base");
        // $file = "processo_{$this->processo->processo}-{$this->processo->fazer}-".now()->format('Y-m-d').".xlsx";
        $file = $nomeCompleto;
        $path = $disk->path("temp/$file");

        IOFactory::createWriter($spreadsheet, 'Xlsx')
            ->setPreCalculateFormulas(false)
            ->save($path);

        return $path;
    }   

    /* CORE */
    private function copiarValoresDoModelo(
        Worksheet $sheetModelo,
        Worksheet $sheetDestino,
        Carbon $inicio,
        Carbon $fim,
        bool $normalizarMesAno,
        string $ultimaColuna,
        string $colunaDataPrincipal,
        string $rangeControle,
        string $colunaControle
    ): void {
                        //    $sheetModelo->getHighestRow() esse pega ultima linha da planilha com valor
                        //    $sheetModelo-getHighestDataRow('A')  esse pega a ultima lihha da coluna a que tem dados
                        //    porem esse ultimo nao tava pegando o valor da col a e sim da col q

                        
                        // VAMOS PEGAR O VALOR DA COL A2  ATE A COL A5845 DA PLAN CONTROLE FREQUENCIA
                        // O CONTROLE DE FREQUENCIA POSSUI O INTERVALO DE DATA ATE 31/12
                        // A DATA INICIO JA ESTA PELA DATA AJUIZAMENTO OU NAO.
                    if ($sheetModelo->getTitle() == "Controles de Frequência modelo") {
                        $valorCellA1 = $sheetModelo->getCell("A2")->getValue();
                         $valorCelulaFinalModelo = $sheetModelo->getCell("A5845")->getValue();
                          // converte datas do Excel
                        $dataExcelIncio = Carbon::instance(
                            Date::excelToDateTimeObject($valorCellA1)
                        );

                        $dataExcelFim = Carbon::instance(
                            Date::excelToDateTimeObject($valorCelulaFinalModelo)
                        );
                        // valida período
                        if ($inicio->lt($dataExcelIncio) || $fim->gt($dataExcelFim)) {
                            throw new \Exception("Insira uma data Valida(inicio ou Fim) dentro dor intervalo da Planilha Modelo: 01/01/2011 ate 31/12/2026");
                        }   
                    }


                   

       
            // fim teste acima.

        
        $ultimaColunaIndex = Coordinate::columnIndexFromString($ultimaColuna);

        // ======================
        // Copia PRINCIPAL
        // ======================

        // dump($sheetModelo->getTitle(), $sheetModelo->getHighestRow());
        $dados = $sheetModelo->rangeToArray(
            "A2:$ultimaColuna".$sheetModelo->getHighestRow(),
            null,
            false,
            false,
            true
        );

        $linhaDestino = 2;
        foreach ($dados as $linhaOrigem => $linha) {
            $valorData = $linha[$colunaDataPrincipal] ?? null;

            $data = $this->extrairData($valorData);
            if (!$data) continue;
            if ($normalizarMesAno) $data->startOfMonth();
            if ($data->lt($inicio)) continue;
            if ($data->gt($fim)) break;

            $this->copiarLinha($sheetModelo, $sheetDestino, $linhaOrigem, $linhaDestino, $ultimaColunaIndex);
            $linhaDestino++;
        }

        // ======================
        // Copia CONTROLE
        // ======================

        
        $linhaDestinoControle = $linhaDestino;

        if($sheetModelo->getTitle()=="Históricos Salariais modelo"){
            $linhaDestinoControle=2;
        }

        // Percorre cada linha do range de controle
        $dadosControle = $sheetModelo->rangeToArray($rangeControle, null, false, false, true, true); 
        // o último "true" calcula fórmulas automaticamente
            
        foreach ($dadosControle as $linhaOrigem => $linha) {               
                    
            // Pega o valor calculado da coluna de data
            $origemCell = $sheetModelo->getCell($colunaControle . $linhaOrigem);
            $valorData = $origemCell->getCalculatedValue();

            // Tenta extrair a data
            $data = $this->extrairData($valorData);

            if (!$data) {
                Log::warning("Linha $linhaOrigem ignorada: data inválida", ['valor' => $valorData]);
                continue; // ignora se não conseguir extrair a data
            }
             
            // em ambos os casos historicossalarial e controle  de frequencia as datas sao do primeiro dia do mes
            // if ($normalizarMesAno) $data->startOfMonth();
             $data->startOfMonth();
            // passei a data de inicio para o primeiro dia do mes para que no controle de frequencia nos dados de controle ele pegue o mes de inicio tambem.
            $inicio->startOfMonth();

            

            if ($data->lt($inicio)) continue;
              
            if ($data->gt($fim)) break;

            // Copia todas as colunas da linha
            foreach ($linha as $coluna => $valor) {
                $origem = $sheetModelo->getCell("$coluna$linhaOrigem");
                $destino = $sheetDestino->getCell("$coluna$linhaDestinoControle");

                // pega valor calculado
                $valor = $origem->getCalculatedValue();
                if ($origem->isFormula()) {
                    $delta = $linhaDestinoControle - $linhaOrigem;
                    $valor = $this->ajustarFormulaLinhas($valor, $delta);
                }

                $destino->setValue($valor);

                // copia estilo usando Worksheet::duplicateStyle()
                $sheetDestino->duplicateStyle(
                    $sheetModelo->getStyle("$coluna$linhaOrigem"),
                    "$coluna$linhaDestinoControle"
                );
            }
            $linhaDestinoControle++;



        }
      
       if ($sheetDestino->getTitle() == 'Históricos Salariais') {
             
            $sheetDestino
            ->getStyle('B'.$linhaDestino.':B194' )
            ->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_NONE);
            
            $sheetDestino
            ->getStyle('AS'.$linhaDestino.':AS194' )
            ->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_NONE);

       }
       if ($sheetDestino->getTitle() == 'Controles de Frequência') {
            $sheetDestino
            ->getStyle('A'.$linhaDestinoControle.':BA6038')
            ->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_NONE);

            $sheetDestino
            ->getStyle('A'.$linhaDestino.':AP6038')
            ->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_NONE);     
                
                // dump('AV'.$linhaDestino.':AV' . $linhaDestinoControle);      

                $sheetDestino
            ->getStyle('AV'.$linhaDestino.':AV' . $linhaDestinoControle)
            ->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_NONE);
            
        }

    }

    private function copiarLinha(Worksheet $modelo, Worksheet $destino, int $linhaOrigem, int $linhaDestino, int $ultimaColunaIndex): void
    {
        for ($col = 1; $col <= $ultimaColunaIndex; $col++) {
            $coluna = Coordinate::stringFromColumnIndex($col);
            $origemCell  = $modelo->getCell($coluna.$linhaOrigem);
            $destinoCell = $destino->getCell($coluna.$linhaDestino);

            $valor = $origemCell->getValue();
            if ($origemCell->isFormula()) {
                $delta = $linhaDestino - $linhaOrigem;
                $valor = $this->ajustarFormulaLinhas($valor, $delta);
            }
            $destinoCell->setValue($valor);
        }
    }

    private function extrairData($valor): ?Carbon
    {
        if (!$valor) return null;

        if (is_numeric($valor)) {
            return Carbon::instance(Date::excelToDateTimeObject((float)$valor));
        }

        // Tenta parse de string
        try {
            return Carbon::parse($valor);
        } catch (\Exception $e) {
            return null;
        }
    }

    private function ajustarFormulaLinhas(string $formula, int $delta): string
    {
        if (!$formula) return $formula;

        // Remove referência a abas modelo
        $formula = preg_replace("/'([^']+?) modelo'!/", "'$1'!", $formula);

        return preg_replace_callback('/(\$?[A-Z]{1,3})(\$?\d+)/', function ($m) use ($delta) {
            $col = $m[1];
            $row = $m[2];
            if (str_starts_with($row, '$')) return $col.$row;
            return $col.(max(1, ((int)$row)+$delta));
        }, $formula);
    }

    private function retornarDataInicio(string $aba): Carbon
    {
        $aju = Carbon::parse($this->processo->ajuizamento);
        $adm = Carbon::parse($this->processo->admissao);

        if ($adm->diffInDays($aju) >= 1827) {
            $base = $aju->copy()->subYears(5)->startOfMonth();
            $cartao = $aju->copy()->subYears(5);
        } else {
            $base = $adm->copy()->startOfMonth();
            $cartao = $adm;
        }

        return $aba === 'Históricos Salariais' ? $base : $cartao;
    }




    // private function gerarReciboPdf(){
    //         $disk = Storage::disk('local');
    //         // dd("teste");

    //     try {
    //     $html = view('inicio.recibo1-pdf', [
    //         'processo' => $this->processo
    //     ])->render();

    //     // dd($html);

    //     } catch (\Throwable $e) {
    //         dd($e->getMessage(), $e->getFile(), $e->getLine());
    //     }

    //     $pdf = Pdf::loadHTML($html);

    //     $file = "recibo_{$this->processo->id}.pdf";
    //     $path = $disk->path("temp/$file");

    //     file_put_contents($path, $pdf->output());

    //     return $path;

    // }
    private function gerarReciboPdf(){
        $disk = Storage::disk('local');
            // dd("teste");
        
        try {
        $html = view('inicio.recibo-pdf', [
            'processo' => $this->processo
        ])->render();

        $pdf = Pdf::loadHTML($html)
                    ->setPaper('A4', 'portrait')
                    ;

        // dd($html);

        } catch (\Throwable $e) {
            dd($e->getMessage(), $e->getFile(), $e->getLine());
        }

        //albano 0011825-02.2015.5.01.0053 CEI-recibo.pdf 
        // $this->retornarNomeArquivo("pdf")
        
        $nomeCompleto = $this->retornarNomeArquivo("pdf", "recibo");
        $file = $nomeCompleto;
        $path = $disk->path("temp/$file");

        file_put_contents($path, $pdf->output());

        return $path;

    }
    /**
     * Gera o nome final do arquivo com base no tipo e processo
     * advogado & " " & processo & " " & nome_parte & " - " & nome_arquivo
     * @param string $tipoArquivo Tipo do arquivo (ex: PDF, ZIP)
     * @param string $nome_arquivo  nome final proximo da extensaos, para o word nao existe 
     * @return string Nome formatado do arquivo
     */
    public function retornarNomeArquivo($tipoArquivo, $nome_arquivo): string{
       
        $nomeParte = "";
        $numeroProcesso = $this->processo->processo ?? "";
        $advogado = optional($this->processo->advogado)->apelido ??"";
        $nomeCompletoArquivo="";
        
        if($this->processo->tipo==1){
            $nomeParte = $this->processo->reclamante ?? "";
           
        }else{            
            $nomeParte = optional($this->processo->processoReclamada)->apelido ?? "";
            if($nomeParte==""){
                $nomeParte = $this->processo->reclamante ?? "";
            }
            
        }
        $nomeParte = explode(' ', trim($nomeParte))[0] ?? "";
        $nomeParte = strtoupper($nomeParte ??"");
        // var_dump($nomeParte);
        if ($tipoArquivo === "pdf" || $tipoArquivo === "xlsx") {
            $nomeCompletoArquivo = $advogado . " " . $numeroProcesso . " " . $nomeParte . "-" . $nome_arquivo;
            $nomeCompletoArquivo .= $tipoArquivo === "pdf" ? ".pdf" : ".xlsx";
}
        if($tipoArquivo==="docx"){            
             $nomeCompletoArquivo = $advogado . " " . $numeroProcesso . " " . $nomeParte . ".docx";
            
        }

        // if($tipoArquivo==="xlsx"){            
        // $nomeCompletoArquivo = $advogado . " " . $numeroProcesso . " " . $nomeParte . "-" . $nome_arquivo . ".xlsx";
        // }

       
    
    return $nomeCompletoArquivo;


    }

    public function gerarConprocPdf(){
        $disk = Storage::disk('local');
            // dd("teste");
        
        $demissao = $this->processo->demissao;
        $admissao = $this->processo->admissao;

        $avisoPrevio = $this->calcularAvisoPrevio($admissao,$demissao);
        $this->processo->aviso_previo = $avisoPrevio;
        // dd($this->processo);

        try {
            
        $html = view('inicio.recibo-advogado-pdf', [
            'processo' => $this->processo
        ])->render();

        $pdf = Pdf::loadHTML($html)
                    ->setPaper('A4', 'portrait')
                    ;

        // dd($html);

        } catch (\Throwable $e) {
            dd($e->getMessage(), $e->getFile(), $e->getLine());
        }

        //albano 0011825-02.2015.5.01.0053 CEI-recibo.pdf 
        // $this->retornarNomeArquivo("pdf")
        
        $nomeCompleto = $this->retornarNomeArquivo("pdf", "conproc");
        
        $file = $nomeCompleto;
        $path = $disk->path("temp/$file");

        file_put_contents($path, $pdf->output());

        return $path;

    

    }
    
    /**
     * Calcula o aviso prévio com base nas datas de admissão e demissão.
     *
     * Retorna:
     * - dias_aviso_previo (int)
     * - data_projecao_aviso (Carbon)
     *
     * @param string|Carbon $dataAdmissao
     * @param string|Carbon $dataDemissao
     * @return array{
     *     dias_aviso_previo: int,
     *     data_projecao_aviso: \Carbon\Carbon
     * }
     */

    function calcularAvisoPrevio($dataAdmissao, $dataDemissao)
    {
        
        if (!$dataAdmissao || !$dataDemissao) {
            return [
                'diasAP' => null,
                'projecao' => null
            ];
        }

        $admissao = Carbon::parse($dataAdmissao);
        $demissao = Carbon::parse($dataDemissao);

       
        // regra da data
        $limite = Carbon::create(2011, 11, 11);

        // tabela
        $tabela = [
            0 => 30,
            365 => 33,
            730 => 36,
            1095 => 39,
            1460 => 42,
            1825 => 45,
            2190 => 48,
            2555 => 51,
            2920 => 54,
            3285 => 57,
            3650 => 60,
            4015 => 63,
            4380 => 66,
            4745 => 69,
            5110 => 72,
            5475 => 75,
            5840 => 78,
            6205 => 81,
            6570 => 84,
            6935 => 87,
            7300 => 90,
        ];

        // cálculo do aviso
        if ($demissao->gte($limite)) {
 

            $diasTrabalhados = $admissao->diffInDays($demissao);
             
            $aviso = 30;
              
            foreach ($tabela as $diasBase => $valor) {
                if ($diasTrabalhados >= $diasBase) {
                    $aviso = $valor;
                } else {
                    break;
                }

            }
                    
        } else {
            $aviso = 30;
        }


        
        // soma +3
        $diasAP = $aviso + 3;

        // limite máximo
        if($diasAP<90){
           $diasAP = $diasAP - 3; 
        }else{
            $diasAP = 90;
        }
        // $diasAP = min($diasAP, 90);

        // projeção (dias corridos)
        $projecao = $demissao->copy()->addDays($diasAP);
           
        return [
            'diasAP' => $diasAP,
            'projecao' => $projecao->format('d/m/Y')
        ];
    }


}
// historioc salariais
// 21/01/2026 ajuizamento        // 01 / 01/2021
// 15/07/2020	 admissao
// 15/07/2025 demissao           //01/07/2025

// historico frequencias

// 21/01/2026 ajuizamento       
// 15/07/2020	 admissao       
// 15/07/2025 demissao           //01/07/2025