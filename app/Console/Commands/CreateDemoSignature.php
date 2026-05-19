<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class CreateDemoSignature extends Command
{
    protected $signature = 'signature:demo {tenant_id=2}';
    protected $description = 'Create demo company signature for tenant';

    public function handle()
    {
        $tenantId = $this->argument('tenant_id');
        
        // Create signature directory
        $signatureDir = "compliance/signatures/company/{$tenantId}";
        if (!Storage::disk('local')->exists($signatureDir)) {
            Storage::disk('local')->makeDirectory($signatureDir);
        }

        // Generate demo signature image
        $signaturePath = storage_path("app/{$signatureDir}/company_signature.png");
        $this->createDemoSignatureImage($signaturePath);

        // Calculate hash
        $signatureHash = hash_file('sha256', $signaturePath);
        $relativePath = "{$signatureDir}/company_signature.png";

        // Insert into compliance_signatures table
        DB::table('compliance_signatures')->insert([
            'tenant_id' => $tenantId,
            'batch_id' => null,
            'form_code' => 'COMPANY_MASTER',
            'signature_type' => 'IMAGE',
            'signature_data' => $relativePath,
            'signature_hash' => $signatureHash,
            'document_hash' => null,
            'signed_by' => 'Company Administrator',
            'signed_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->info("Company signature created for tenant {$tenantId}");
        $this->info("Path: {$relativePath}");
        $this->info("Hash: {$signatureHash}");
        
        return 0;
    }

    private function createDemoSignatureImage(string $path): void
    {
        $width = 300;
        $height = 100;
        $image = imagecreatetruecolor($width, $height);
        
        $white = imagecolorallocate($image, 255, 255, 255);
        $blue = imagecolorallocate($image, 0, 102, 204);
        
        imagefill($image, 0, 0, $white);
        
        // Draw signature-like text
        imagestring($image, 5, 20, 20, 'Authorized Signatory', $blue);
        imagestring($image, 3, 20, 50, 'Company Administrator', $blue);
        imagestring($image, 2, 20, 75, date('Y-m-d'), $blue);
        
        imagepng($image, $path);
        imagedestroy($image);
    }
}
