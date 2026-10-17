<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('auto_deploy_configs', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('server_id')->nullable()->comment('ID server yang di-deploy');
            $table->string('action')->default('deploy_new'); // deploy_new, configure_existing
            $table->unsignedInteger('preset')->nullable(); // 1-8 untuk RAM preset
            $table->string('username', 100)->nullable();
            $table->string('ssh_host', 255)->nullable();
            $table->unsignedInteger('ssh_port')->default(22);
            $table->string('ssh_username', 100)->default('root');
            $table->text('ssh_private_key')->nullable()->nullable();
            $table->boolean('open_port_http')->default(false);
            $table->boolean('open_port_https')->default(false);
            $table->boolean('add_cloudflare_tunnel')->default(false);
            $table->text('cloudflare_token')->nullable();
            $table->string('cloudflare_tunnel_name', 100)->nullable();
            $table->string('cloudflare_tunnel_url', 255)->nullable();
            $table->boolean('tunnel_active')->default(false);
            $table->enum('status', ['pending', 'configuring', 'completed', 'failed'])->default('pending');
            $table->text('error_message')->nullable();
            $table->timestamps();
            $table->index(['server_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('auto_deploy_configs');
    }
};
