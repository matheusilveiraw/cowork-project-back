<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;

class InvoicesController extends BaseController
{
    protected $model;

    public function __construct()
    {
        $this->model = new \App\Models\InvoicesModel();
    }

    public function generateInvoice($deskRentalId)
    {
        $deskRentalModel = new \App\Models\DeskRentalsModel();
        $deskRental = $deskRentalModel->find($deskRentalId);

        if (!$deskRental) {
            return $this->response
                ->setStatusCode(404)
                ->setJSON(['error' => 'Aluguel não encontrado']);
        }

        $customerModel = new \App\Models\CustomersModel();
        $customer = $customerModel->find($deskRental['idCustomer']);

        $planModel = new \App\Models\RentalPlansModel();
        $plan = $planModel->find($deskRental['idPlan']);

        $companyModel = new \App\Models\CompanyDataModel();
        $company = $companyModel->first();

        $invoiceNumber = $this->generateInvoiceNumber();

        $invoiceData = [
            'invoice_number' => $invoiceNumber,
            'idDeskRental' => $deskRentalId,
            'issue_date' => date('Y-m-d'),
            'due_date' => date('Y-m-d', strtotime('+7 days')),
            'total_amount' => $deskRental['total_price'],
            'status' => 'pending'
        ];

        if ($this->model->insert($invoiceData)) {
            $invoiceId = $this->model->getInsertID();
            
            $xmlContent = $this->generateNFeXML($invoiceData, $deskRental, $customer, $plan, $company);
            
            $this->model->update($invoiceId, [
                'xml_content' => $xmlContent,
                'access_key' => $this->generateAccessKey()
            ]);

            return $this->response
                ->setStatusCode(201)
                ->setJSON([
                    'status' => 'success',
                    'message' => 'Nota fiscal gerada com sucesso',
                    'data' => array_merge($invoiceData, ['idInvoice' => $invoiceId])
                ]);
        }

        return $this->response
            ->setStatusCode(400)
            ->setJSON(['error' => 'Erro ao gerar nota fiscal']);
    }

    private function generateInvoiceNumber()
    {
        $year = date('Y');
        $lastInvoice = $this->model->orderBy('idInvoice', 'DESC')->first();
        
        $sequence = 1;
        if ($lastInvoice) {
            $lastSequence = (int) substr($lastInvoice['invoice_number'], -6);
            $sequence = $lastSequence + 1;
        }

        return $year . str_pad($sequence, 6, '0', STR_PAD_LEFT);
    }

    private function generateAccessKey()
    {
        return substr(md5(uniqid(rand(), true)), 0, 44);
    }

    private function generateNFeXML($invoice, $deskRental, $customer, $plan, $company)
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>
<enviNFe xmlns="http://www.portalfiscal.inf.br/nfe" versao="4.00">
    <NFe>
        <infNFe Id="NFe' . $invoice['access_key'] . '" versao="4.00">
            <ide>
                <cUF>35</cUF>
                <cNF>' . substr($invoice['access_key'], 20, 8) . '</cNF>
                <natOp>Prestação de Serviço</natOp>
                <mod>55</mod>
                <serie>1</serie>
                <nNF>' . substr($invoice['invoice_number'], -6) . '</nNF>
                <dhEmi>' . date('c') . '</dhEmi>
                <tpNF>1</tpNF>
                <idDest>1</idDest>
                <cMunFG>3550308</cMunFG>
                <tpImp>1</tpImp>
                <tpEmis>1</tpEmis>
                <cDV>' . substr($invoice['access_key'], -1) . '</cDV>
                <tpAmb>2</tpAmb>
                <finNFe>1</finNFe>
                <indFinal>1</indFinal>
                <indPres>1</indPres>
                <procEmi>0</procEmi>
                <verProc>1.0</verProc>
            </ide>
            <emit>
                <CNPJ>' . preg_replace('/[^0-9]/', '', $company['cnpj']) . '</CNPJ>
                <xNome>' . $company['company_name'] . '</xNome>
                <xFant>' . $company['trading_name'] . '</xFant>
                <enderEmit>
                    <xLgr>' . $company['address_street'] . '</xLgr>
                    <nro>' . $company['address_number'] . '</nro>
                    <xBairro>' . $company['address_neighborhood'] . '</xBairro>
                    <cMun>' . $company['address_city'] . '</cMun>
                    <xMun>' . $company['address_city'] . '</xMun>
                    <UF>' . $company['address_state'] . '</UF>
                    <CEP>' . preg_replace('/[^0-9]/', '', $company['address_zipcode']) . '</CEP>
                    <cPais>1058</cPais>
                    <xPais>Brasil</xPais>
                    <fone>' . preg_replace('/[^0-9]/', '', $company['phone']) . '</fone>
                </enderEmit>
                <IE>' . $company['state_registration'] . '</IE>
                <IM>' . $company['municipal_registration'] . '</IM>
                <CNAE>7729200</CNAE>
                <CRT>3</CRT>
            </emit>
            <dest>
                <xNome>' . $customer['nameCustomer'] . '</xNome>
                <enderDest>
                    <xLgr>' . $customer['addressCustomer'] . '</xLgr>
                    <xBairro>Centro</xBairro>
                    <cMun>3550308</cMun>
                    <xMun>São Paulo</xMun>
                    <UF>SP</UF>
                    <CEP>01001000</CEP>
                    <cPais>1058</cPais>
                    <xPais>Brasil</xPais>
                </enderDest>
                <indIEDest>9</indIEDest>
            </dest>
            <det nItem="1">
                <prod>
                    <cProd>COWORK' . $deskRental['idDesk'] . '</cProd>
                    <cEAN>SEM GTIN</cEAN>
                    <xProd>Aluguel de Mesa - ' . $plan['planName'] . '</xProd>
                    <NCM>99999999</NCM>
                    <CEST>9999999</CEST>
                    <CFOP>5932</CFOP>
                    <uCom>UN</uCom>
                    <qCom>1.0000</qCom>
                    <vUnCom>' . number_format($deskRental['total_price'], 2, '.', '') . '</vUnCom>
                    <vProd>' . number_format($deskRental['total_price'], 2, '.', '') . '</vProd>
                    <cEANTrib>SEM GTIN</cEANTrib>
                    <uTrib>UN</uTrib>
                    <qTrib>1.0000</qTrib>
                    <vUnTrib>' . number_format($deskRental['total_price'], 2, '.', '') . '</vUnTrib>
                    <indTot>1</indTot>
                </prod>
                <imposto>
                    <vTotTrib>0.00</vTotTrib>
                    <ICMS>
                        <ICMS00>
                            <orig>0</orig>
                            <CST>00</CST>
                            <modBC>3</modBC>
                            <vBC>' . number_format($deskRental['total_price'], 2, '.', '') . '</vBC>
                            <pICMS>18.00</pICMS>
                            <vICMS>' . number_format($deskRental['total_price'] * 0.18, 2, '.', '') . '</vICMS>
                        </ICMS00>
                    </ICMS>
                </imposto>
            </det>
            <total>
                <ICMSTot>
                    <vBC>' . number_format($deskRental['total_price'], 2, '.', '') . '</vBC>
                    <vICMS>' . number_format($deskRental['total_price'] * 0.18, 2, '.', '') . '</vICMS>
                    <vICMSDeson>0.00</vICMSDeson>
                    <vFCP>0.00</vFCP>
                    <vBCST>0.00</vBCST>
                    <vST>0.00</vST>
                    <vFCPST>0.00</vFCPST>
                    <vFCPSTRet>0.00</vFCPSTRet>
                    <vProd>' . number_format($deskRental['total_price'], 2, '.', '') . '</vProd>
                    <vFrete>0.00</vFrete>
                    <vSeg>0.00</vSeg>
                    <vDesc>0.00</vDesc>
                    <vII>0.00</vII>
                    <vIPI>0.00</vIPI>
                    <vIPIDevol>0.00</vIPIDevol>
                    <vPIS>0.00</vPIS>
                    <vCOFINS>0.00</vCOFINS>
                    <vOutro>0.00</vOutro>
                    <vNF>' . number_format($deskRental['total_price'], 2, '.', '') . '</vNF>
                    <vTotTrib>0.00</vTotTrib>
                </ICMSTot>
            </total>
            <transp>
                <modFrete>9</modFrete>
            </transp>
            <cobr>
                <fat>
                    <nFat>' . $invoice['invoice_number'] . '</nFat>
                    <vOrig>' . number_format($deskRental['total_price'], 2, '.', '') . '</vOrig>
                    <vDesc>0.00</vDesc>
                    <vLiq>' . number_format($deskRental['total_price'], 2, '.', '') . '</vLiq>
                </fat>
                <dup>
                    <nDup>001</nDup>
                    <dVenc>' . $invoice['due_date'] . '</dVenc>
                    <vDup>' . number_format($deskRental['total_price'], 2, '.', '') . '</vDup>
                </dup>
            </cobr>
            <infAdic>
                <infCpl>Período: ' . $deskRental['startPeriod'] . ' a ' . $deskRental['endPeriod'] . '</infCpl>
            </infAdic>
        </infNFe>
    </NFe>
</enviNFe>';

        return $xml;
    }

    public function markAsPaid($invoiceId)
    {
        $data = $this->request->getJSON(true);

        if (!$this->model->update($invoiceId, [
            'status' => 'paid',
            'payment_date' => date('Y-m-d'),
            'payment_method' => $data['payment_method'] ?? 'cash'
        ])) {
            return $this->response
                ->setStatusCode(400)
                ->setJSON(['error' => 'Erro ao atualizar nota fiscal']);
        }

        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Nota fiscal marcada como paga'
        ]);
    }

    public function getInvoice($invoiceId)
    {
        $invoice = $this->model->find($invoiceId);

        if (!$invoice) {
            return $this->response
                ->setStatusCode(404)
                ->setJSON(['error' => 'Nota fiscal não encontrada']);
        }

        return $this->response->setJSON($invoice);
    }

    public function getAllInvoices()
    {
        return $this->response->setJSON($this->model->findAll());
    }
}