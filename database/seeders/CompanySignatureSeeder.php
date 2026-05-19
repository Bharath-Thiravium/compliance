<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

class CompanySignatureSeeder extends Seeder
{
    public function run(): void
    {
        $tenantId = 2; // FULL subscription tenant

        // Create signature directory
        $signatureDir = "compliance/signatures/company/{$tenantId}";
        if (!Storage::disk('local')->exists($signatureDir)) {
            Storage::disk('local')->makeDirectory($signatureDir);
        }

        // Generate demo signature image (simple PNG)
        $signaturePath = storage_path("app/{$signatureDir}/company_signature.png");
        $this->createDemoSignatureImage($signaturePath);

        // Calculate hash
        $signatureHash = hash_file('sha256', $signaturePath);
        $documentHash = hash_file('sha256', $signaturePath);
        $relativePath = "{$signatureDir}/company_signature.png";

        // Get branch and batch IDs
        $branchId = DB::table('branches')
            ->where('tenant_id', $tenantId)
            ->value('id');
        $batchId = DB::table('compliance_execution_batches')
            ->where('tenant_id', $tenantId)
            ->value('id');

        // Insert into compliance_signatures table
        DB::table('compliance_signatures')->insert([
            'tenant_id'              => $tenantId,
            'branch_id'              => $branchId,
            'form_code'              => 'COMPANY_MASTER',
            'batch_id'               => $batchId,
            'signed_by_user_id'      => 1,
            'signatory_name'         => 'Company Administrator',
            'signatory_designation'  => 'Authorized Signatory',
            'signature_type'         => 'IMAGE',
            'signature_path'         => $relativePath,
            'signature_hash'         => $signatureHash,
            'document_hash'          => $documentHash,
            'ip_address'             => '127.0.0.1',
            'signed_at'              => now(),
            'created_at'             => now(),
            'updated_at'             => now(),
        ]);

        $this->command->info("Company signature created for tenant {$tenantId}");
    }

    private function createDemoSignatureImage(string $path): void
    {
        $width = 300;
        $height = 100;

        // Ensure directory exists
        $directory = dirname($path);
        if (!File::exists($directory)) {
            File::makeDirectory($directory, 0755, true);
        }

        $image = imagecreatetruecolor($width, $height);

        $white = imagecolorallocate($image, 255, 255, 255);
        $blue = imagecolorallocate($image, 0, 102, 204);

        imagefill($image, 0, 0, $white);

        imagestring($image, 5, 20, 20, 'Authorized Signatory', $blue);
        imagestring($image, 3, 20, 50, 'Company Administrator', $blue);
        imagestring($image, 2, 20, 75, date('Y-m-d'), $blue);

        imagepng($image, $path);
        imagedestroy($image);
    }
}
