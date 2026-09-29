<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Zatca_invoice
{
    private $ci;

    public function __construct()
    {
        $this->ci = &get_instance();
    }

    /**
     * Generate SECP256K1 Key Pair (Private & Public Key)
     * ZATCA requires Elliptic Curve Logic
     */
    public function generate_key_pair()
    {
        $config = [
            'config' => $this->get_openssl_config_path(),
            'private_key_type' => OPENSSL_KEYTYPE_EC,
            'curve_name' => 'secp256k1'
        ];

        $res = openssl_pkey_new($config);

        if (!$res) {
            log_activity('ZATCA: OpenSSL Error: ' . openssl_error_string());
            return false;
        }

        openssl_pkey_export($res, $privKey, null, $config);
        $pubKeyDetails = openssl_pkey_get_details($res);
        $pubKey = $pubKeyDetails['key'];

        return [
            'private_key' => $privKey,
            'public_key'  => $pubKey
        ];
    }

    /**
     * Generate CSR (Certificate Signing Request) using custom Config for ZATCA OIDs
     */
    public function generate_csr($data)
    {
        $cnfDetails = $this->create_custom_openssl_config($data);
        $configPath = $cnfDetails['path'];
        
        $config = [
            'config' => $configPath,
            'private_key_type' => OPENSSL_KEYTYPE_EC,
            'curve_name' => 'secp256k1',
            'digest_alg' => 'sha256'
        ];
        
        $privKeyStr = get_option('zatca_private_key');
        
        if(empty($privKeyStr)){
             // If no private key exists, generate one now
             $keys = $this->generate_key_pair();
             if(!$keys) return false;
             
             update_option('zatca_private_key', $keys['private_key']);
             update_option('zatca_public_key', $keys['public_key']);
             $privKeyStr = $keys['private_key'];
        }
        
        $privKey = openssl_pkey_get_private($privKeyStr);

        // We pass NULL for dn because it's defined in the config file
        $csr = openssl_csr_new(null, $privKey, $config); 
        
        if (!$csr) {
             log_activity('ZATCA CSR Error: ' . openssl_error_string());
             // Cleanup
             @unlink($configPath);
             return false;
        }

        openssl_csr_export($csr, $csrOut);
        
        // Cleanup temp config
        @unlink($configPath);
        
        return $csrOut;
    }

    /**
     * Create a temporary OpenSSL config file for ZATCA
     */
    private function create_custom_openssl_config($data)
    {
        $tmpParams = [
            'CN' => $data['common_name'],
            'OU' => $data['organization_unit'],
            'O' => $data['organization_name'],
            'C' => $data['country_name'],
            'SN' => '1-TST|2-TST|3-ed22f1d8-e6a2-1118-9b58-d9a8f11e445f', // Example Serial, should be dynamic
            'UID' => $data['vat_number'], // 3000...
            'TITLE' => '1100', // Invoice Type (1100 for Tax, 0100 for Simplified) - Simplified usually
            'REGISTERED_ADDRESS' => $data['address'],
            'CATEGORY' => 'Industry'
        ];

        // Template for ZATCA
        // Note: 2.5.4.97 is organizationIdentifier.
        // PHP OpenSSL might default names, so strict OID mapping in [ req_distinguished_name ] is key.
        
        $cnfContent = <<<EOD
[ req ]
default_bits = 2048
emailAddress = email@example.com
req_extensions = v3_req
x509_extensions = v3_ca
prompt = no
distinguished_name = req_distinguished_name

[ req_distinguished_name ]
C = {$tmpParams['C']}
OU = {$tmpParams['OU']}
O = {$tmpParams['O']}
CN = {$tmpParams['CN']}
# Custom OIDs are tricky in simple INI, but standard field mapping:
# 2.5.4.4 (Surname) -> SN (Used for Serial Number in ZATCA)
SN = {$tmpParams['SN']}
# 2.5.4.97 (OrganizationIdentifier) -> VAT
organizationIdentifier = {$tmpParams['UID']}
# 2.5.4.26 (RegisteredAddress) -> Address
registeredAddress = {$tmpParams['REGISTERED_ADDRESS']}
# 2.5.4.15 (BusinessCategory) -> Category
businessCategory = {$tmpParams['CATEGORY']}
# 0.9.2342.19200300.100.1.5 (UID) -> Used for Valid from/to sometimes, but ZATCA uses special fields.
# For simplicity, we stick to mandatory ones.

[ v3_req ]
basicConstraints = CA:FALSE
keyUsage = nonRepudiation, digitalSignature, keyEncipherment

[ v3_ca ]
basicConstraints = CA:FALSE
EOD;

        $tempPath = TEMP_FOLDER . 'zatca_openssl_' . uniqid() . '.cnf';
        file_put_contents($tempPath, $cnfContent);
        
        return ['path' => $tempPath];
    }


    /**
     * Hash Invoice XML (SHA256)
     * Must be canonicalized first!
     */
    public function calculate_invoice_hash($xml_content)
    {
        // PENDING: XML Canonicalization (c14n) logic is needed here before hashing.
        // For now, raw hash for structure.
        return hash('sha256', $xml_content);
    }

    /**
     * Sign the Hash with Private Key (ECDSA)
     */
    public function sign_invoice_hash($hash_string)
    {
        $private_key = get_option('zatca_private_key');
        
        // Decode hex hash to binary
        // $binary_hash = hex2bin($hash_string);
        
        // OpenSSL Sign (ECDSA)
        // Note: openssl_sign usually takes data, hashes it, then signs.
        // If we already have the hash, this might be tricky with standard PHP OpenSSL without raw flag.
        // A common approach is to sign the data directly.
        
        // Returning placeholder for now, as we need the XML content to sign properly.
        return ''; 
    }

    /**
     * Generate UBL 2.1 XML for ZATCA
     */
    public function generate_invoice_xml($invoice_id)
    {
        $this->ci->load->model('invoices_model');
        $invoice = $this->ci->invoices_model->get($invoice_id);
        
        // Basic mapping - In a real scenario, this is huge.
        // We will do a minimal valid UBL 2.1 structure.
        
        $xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?>
<Invoice xmlns="urn:oasis:names:specification:ubl:schema:xsd:Invoice-2" xmlns:cac="urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2" xmlns:cbc="urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2" xmlns:ext="urn:oasis:names:specification:ubl:schema:xsd:CommonExtensionComponents-2"></Invoice>');

        // UBL Extension for ZATCA Signatures (Placeholder structure)
        $exts = $xml->addChild('ext:UBLExtensions');
        $ext = $exts->addChild('ext:UBLExtension');
        $ext->addChild('ext:ExtensionURI', 'urn:oasis:names:specification:ubl:dsig:enveloped:xades');
        $content = $ext->addChild('ext:ExtensionContent');
        $sig = $content->addChild('sig:UBLDocumentSignatures', '', 'urn:oasis:names:specification:ubl:schema:xsd:CommonSignatureComponents-2');
        
        // Standard Fields
        $xml->addChild('cbc:ProfileID', 'reporting:1.0');
        $xml->addChild('cbc:ID', $invoice->number);
        $xml->addChild('cbc:UUID', bin2hex(random_bytes(16))); // Should be deterministic if regenerating
        $xml->addChild('cbc:IssueDate', $invoice->date);
        $xml->addChild('cbc:IssueTime', date('H:i:s'));
        $xml->addChild('cbc:InvoiceTypeCode', '388', '', ['name' => '0100000']); // 0100000 = Standard, 0200000 = Simplified
        
        // Supplier
        $supplier = $xml->addChild('cac:AccountingSupplierParty');
        $party = $supplier->addChild('cac:Party');
        // ... Fill detailed Supplier Info from Options
        
        // Customer
        $customer = $xml->addChild('cac:AccountingCustomerParty');
        $partyC = $customer->addChild('cac:Party');
        // ... Fill detailed Customer Info from Invoice->client
        
        // Totals
        //$monetary = $xml->addChild('cac:LegalMonetaryTotal');
        //$monetary->addChild('cbc:TaxExclusiveAmount', $invoice->subtotal, ['currencyID' => 'SAR']);
        //$monetary->addChild('cbc:TaxInclusiveAmount', $invoice->total, ['currencyID' => 'SAR']);
        //$monetary->addChild('cbc:PayableAmount', $invoice->total, ['currencyID' => 'SAR']);
        
        // Return raw XML string
        return $xml->asXML();
    }

    private function get_openssl_config_path()
    {
        // Try common locations
        $paths = [
            'C:/xampp/php/extras/ssl/openssl.cnf',
            'C:/xampp2/php/extras/ssl/openssl.cnf',
            'C:/wamp/bin/apache/apache2.4.51/conf/openssl.cnf',
        ];

        foreach($paths as $path){
            if(file_exists($path)){
                return $path;
            }
        }
        return null; // Let PHP default try
    }
}
