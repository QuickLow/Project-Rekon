erDiagram
    USERS {
        bigint id PK
        varchar name
        varchar username UNIQUE "nullable"
        varchar email UNIQUE
        timestamp email_verified_at "nullable"
        varchar password
        varchar role "default: user (lalu dimigrasi user->admin)"
        varchar remember_token "nullable"
        timestamp created_at
        timestamp updated_at
    }

    REKON_PROJECTS {
        bigint id PK
        varchar project_name
        timestamp created_at
        timestamp updated_at
    }

    REKON_ITEMS {
        bigint id PK
        bigint project_id FK
        varchar mitra_name
        varchar lop_name
        varchar designator
        decimal qty_gudang "15,2 default 0"
        decimal qty_ta "15,2 default 0"
        decimal qty_mitra "15,2 default 0"
        timestamp created_at
        timestamp updated_at
    }

    SESSIONS {
        varchar id PK
        bigint user_id "nullable, indexed (tanpa FK)"
        varchar ip_address "len 45, nullable"
        text user_agent "nullable"
        longtext payload
        int last_activity "indexed"
    }

    PASSWORD_RESET_TOKENS {
        varchar email PK
        varchar token
        timestamp created_at "nullable"
    }

    CACHE {
        varchar key PK
        mediumtext value
        int expiration
    }

    CACHE_LOCKS {
        varchar key PK
        varchar owner
        int expiration
    }

    JOBS {
        bigint id PK
        varchar queue "indexed"
        longtext payload
        tinyint attempts
        int reserved_at "nullable"
        int available_at
        int created_at
    }

    JOB_BATCHES {
        varchar id PK
        varchar name
        int total_jobs
        int pending_jobs
        int failed_jobs
        longtext failed_job_ids
        mediumtext options "nullable"
        int cancelled_at "nullable"
        int created_at
        int finished_at "nullable"
    }

    FAILED_JOBS {
        bigint id PK
        varchar uuid UNIQUE
        text connection
        text queue
        longtext payload
        longtext exception
        timestamp failed_at
    }

    REKON_PROJECTS ||--o{ REKON_ITEMS : "has many (FK project_id, cascade)"
    USERS ||--o{ SESSIONS : "logical (no FK in migration)"<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('username')->nullable()->unique()->after('name');
            $table->string('role')->default('user')->after('password');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['username']);
            $table->dropColumn(['username', 'role']);
        });
    }
};
