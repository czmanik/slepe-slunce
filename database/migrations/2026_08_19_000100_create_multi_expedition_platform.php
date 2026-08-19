<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('expeditions')) {
            Schema::create('expeditions', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('organizer_user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('name', 180);
                $table->string('slug', 190)->unique();
                $table->string('short_description', 500)->nullable();
                $table->longText('description')->nullable();
                $table->dateTime('start_at')->nullable()->index();
                $table->dateTime('end_at')->nullable()->index();
                $table->string('timezone', 64)->default('Europe/Prague');
                $table->string('publication_status', 20)->default('draft')->index();
                $table->string('status_override', 20)->nullable()->index();
                $table->boolean('is_featured')->default(false)->index();
                $table->string('hero_image')->nullable();
                $table->string('hero_alt', 300)->nullable();
                $table->boolean('registration_enabled')->default(false);
                $table->json('allowed_registration_modes')->nullable();
                $table->unsignedInteger('capacity')->nullable();
                $table->unsignedInteger('public_capacity')->nullable();
                $table->dateTime('registration_opens_at')->nullable();
                $table->dateTime('registration_closes_at')->nullable();
                $table->decimal('price_czk', 12, 2)->nullable();
                $table->decimal('price_eur', 12, 2)->nullable();
                $table->unsignedInteger('reservation_hold_hours')->default(48);
                $table->string('leader_name', 160)->nullable();
                $table->string('contact_email', 190)->nullable();
                $table->string('contact_phone', 50)->nullable();
                $table->text('departure_details')->nullable();
                $table->text('transport_details')->nullable();
                $table->text('accommodation_details')->nullable();
                $table->text('accessibility_details')->nullable();
                $table->text('included_services')->nullable();
                $table->text('cancellation_terms')->nullable();
                $table->unsignedInteger('minimum_participants')->nullable();
                $table->boolean('archive_member_locations')->default(false);
                $table->unsignedInteger('location_retention_days')->nullable();
                $table->json('settings')->nullable();
                $table->timestamps();
            });
        }

        $legacyExpeditionId = DB::table('expeditions')->where('slug', 'slepe-slunce-2026')->value('id');
        if (! $legacyExpeditionId) {
            $legacyExpeditionId = DB::table('expeditions')->insertGetId([
                'name' => 'Slepé Slunce 2026',
                'slug' => 'slepe-slunce-2026',
                'short_description' => 'První expedice party kamarádů za úplným zatměním Slunce ve Španělsku.',
                'start_at' => '2026-08-10 00:00:00',
                'end_at' => '2026-08-16 23:59:59',
                'timezone' => 'Europe/Prague',
                'publication_status' => 'published',
                'is_featured' => true,
                'registration_enabled' => false,
                'allowed_registration_modes' => json_encode([]),
                'reservation_hold_hours' => 48,
                'archive_member_locations' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        if (! Schema::hasTable('author_expedition')) {
            Schema::create('author_expedition', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('author_id')->constrained()->cascadeOnDelete();
                $table->foreignId('expedition_id')->constrained()->cascadeOnDelete();
                $table->string('role', 120)->nullable();
                $table->text('expedition_bio')->nullable();
                $table->boolean('is_leader')->default(false);
                $table->unsignedInteger('sort_order')->default(0);
                $table->unique(['author_id', 'expedition_id']);
            });
        }

        if (! Schema::hasTable('locations')) {
            Schema::create('locations', function (Blueprint $table): void {
                $table->id();
                $table->string('name', 180);
                $table->string('address', 300)->nullable();
                $table->string('country_code', 2)->nullable();
                $table->decimal('latitude', 10, 7);
                $table->decimal('longitude', 10, 7);
                $table->timestamps();
                $table->index(['latitude', 'longitude']);
            });
        }

        if (! Schema::hasColumn('posts', 'expedition_id')) {
            Schema::table('posts', function (Blueprint $table): void {
                $table->foreignId('expedition_id')->nullable()->after('created_by')->constrained()->nullOnDelete();
                $table->string('notification_frequency', 20)->default('none')->after('published_at')->index();
                $table->dateTime('notification_sent_at')->nullable()->after('notification_frequency')->index();
            });
        }

        if (! Schema::hasColumn('route_points', 'expedition_id')) {
            Schema::table('route_points', function (Blueprint $table): void {
                $table->foreignId('expedition_id')->nullable()->after('id')->constrained()->cascadeOnDelete();
                $table->foreignId('location_id')->nullable()->after('expedition_id')->constrained()->nullOnDelete();
            });
        }

        if (! Schema::hasColumn('route_segments', 'expedition_id')) {
            Schema::table('route_segments', function (Blueprint $table): void {
                $table->foreignId('expedition_id')->nullable()->after('id')->constrained()->cascadeOnDelete();
            });
        }

        if (! Schema::hasColumn('expedition_states', 'expedition_id')) {
            Schema::table('expedition_states', function (Blueprint $table): void {
                $table->foreignId('expedition_id')->nullable()->after('id')->constrained()->cascadeOnDelete();
                $table->unique('expedition_id');
            });
        }

        if (! Schema::hasColumn('map_photos', 'expedition_id')) {
            Schema::table('map_photos', function (Blueprint $table): void {
                $table->foreignId('expedition_id')->nullable()->after('id')->constrained()->cascadeOnDelete();
            });
        }

        if (! Schema::hasColumn('member_locations', 'expedition_id')) {
            // MySQL uses the original unique index as the supporting index for
            // member_locations.user_id's foreign key. Give that foreign key a
            // replacement index before removing the one-user-one-location
            // constraint, otherwise MySQL fails with error 1553.
            if (! Schema::hasIndex('member_locations', 'member_locations_user_id_index')) {
                Schema::table('member_locations', fn (Blueprint $table) => $table->index('user_id', 'member_locations_user_id_index'));
            }
            if (Schema::hasIndex('member_locations', 'member_locations_user_id_unique')) {
                Schema::table('member_locations', fn (Blueprint $table) => $table->dropUnique('member_locations_user_id_unique'));
            }
            Schema::table('member_locations', function (Blueprint $table): void {
                $table->foreignId('expedition_id')->nullable()->after('id')->constrained()->cascadeOnDelete();
                $table->unique(['expedition_id', 'user_id']);
            });
        }

        DB::table('posts')->update(['expedition_id' => $legacyExpeditionId]);
        DB::table('route_points')->update(['expedition_id' => $legacyExpeditionId]);
        DB::table('route_segments')->update(['expedition_id' => $legacyExpeditionId]);
        DB::table('expedition_states')->update(['expedition_id' => $legacyExpeditionId]);
        DB::table('map_photos')->update(['expedition_id' => $legacyExpeditionId]);
        DB::table('member_locations')->update(['expedition_id' => $legacyExpeditionId]);

        DB::table('authors')->where('is_expedition_member', true)->orderBy('sort_order')->each(function (object $author) use ($legacyExpeditionId): void {
            DB::table('author_expedition')->insert([
                'author_id' => $author->id,
                'expedition_id' => $legacyExpeditionId,
                'expedition_bio' => $author->bio,
                'sort_order' => $author->sort_order,
            ]);
        });

        Schema::create('program_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('expedition_id')->constrained()->cascadeOnDelete();
            $table->nullableMorphs('item');
            $table->string('kind', 30)->index();
            $table->string('title', 180);
            $table->text('description')->nullable();
            $table->dateTime('starts_at')->nullable()->index();
            $table->dateTime('ends_at')->nullable();
            $table->unsignedInteger('sort_order')->default(0)->index();
            $table->boolean('is_public')->default(true)->index();
            $table->timestamps();
            $table->unique(['item_type', 'item_id']);
        });

        $sortOrder = 10;
        $usedSegments = [];
        $points = DB::table('route_points')->where('expedition_id', $legacyExpeditionId)->orderBy('route_order')->orderBy('id')->get();
        foreach ($points as $point) {
            DB::table('program_items')->insert([
                'expedition_id' => $legacyExpeditionId,
                'item_type' => 'App\\Models\\RoutePoint',
                'item_id' => $point->id,
                'kind' => 'stop',
                'title' => $point->name,
                'description' => $point->description,
                'starts_at' => $point->occurred_at,
                'sort_order' => $sortOrder,
                'is_public' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $sortOrder += 10;

            $segments = DB::table('route_segments')->where('from_point_id', $point->id)->orderBy('sort_order')->orderBy('id')->get();
            foreach ($segments as $segment) {
                $target = DB::table('route_points')->where('id', $segment->to_point_id)->value('name');
                DB::table('program_items')->insert([
                    'expedition_id' => $legacyExpeditionId,
                    'item_type' => 'App\\Models\\RouteSegment',
                    'item_id' => $segment->id,
                    'kind' => 'transfer',
                    'title' => $segment->name ?: $point->name.' → '.$target,
                    'description' => $segment->description,
                    'starts_at' => $segment->departed_at ?: $segment->scheduled_departure_at,
                    'ends_at' => $segment->arrived_at ?: $segment->scheduled_arrival_at,
                    'sort_order' => $sortOrder,
                    'is_public' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $usedSegments[] = $segment->id;
                $sortOrder += 10;
            }
        }

        DB::table('route_segments')->whereNotIn('id', $usedSegments ?: [0])->orderBy('sort_order')->orderBy('id')->each(function (object $segment) use ($legacyExpeditionId, &$sortOrder): void {
            $from = DB::table('route_points')->where('id', $segment->from_point_id)->value('name');
            $to = DB::table('route_points')->where('id', $segment->to_point_id)->value('name');
            DB::table('program_items')->insert([
                'expedition_id' => $legacyExpeditionId,
                'item_type' => 'App\\Models\\RouteSegment',
                'item_id' => $segment->id,
                'kind' => 'transfer',
                'title' => $segment->name ?: $from.' → '.$to,
                'description' => $segment->description,
                'starts_at' => $segment->departed_at ?: $segment->scheduled_departure_at,
                'ends_at' => $segment->arrived_at ?: $segment->scheduled_arrival_at,
                'sort_order' => $sortOrder,
                'is_public' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $sortOrder += 10;
        });

        Schema::create('expedition_registrations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('expedition_id')->constrained()->cascadeOnDelete();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('mode', 30)->index();
            $table->string('status', 30)->default('new')->index();
            $table->string('payment_status', 30)->default('unpaid')->index();
            $table->string('name', 160);
            $table->string('email', 190)->index();
            $table->string('phone', 50)->nullable();
            $table->unsignedInteger('party_size')->default(1);
            $table->string('departure_choice', 250)->nullable();
            $table->text('assistance_needs')->nullable();
            $table->text('dietary_needs')->nullable();
            $table->text('note')->nullable();
            $table->decimal('amount_due', 12, 2)->nullable();
            $table->decimal('amount_paid', 12, 2)->default(0);
            $table->string('currency', 3)->default('CZK');
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->string('discount_note', 500)->nullable();
            $table->dateTime('hold_expires_at')->nullable()->index();
            $table->dateTime('reviewed_at')->nullable();
            $table->dateTime('consent_at');
            $table->string('consent_ip', 45)->nullable();
            $table->timestamps();
        });

        Schema::create('subscribers', function (Blueprint $table): void {
            $table->id();
            $table->string('email', 190)->unique();
            $table->string('name', 160)->nullable();
            $table->string('status', 20)->default('pending')->index();
            $table->boolean('new_expeditions')->default(true);
            $table->boolean('project_news')->default(true);
            $table->boolean('shop_news')->default(false);
            $table->string('confirm_token', 64)->unique();
            $table->string('unsubscribe_token', 64)->unique();
            $table->dateTime('confirmed_at')->nullable();
            $table->dateTime('unsubscribed_at')->nullable();
            $table->dateTime('consent_at');
            $table->string('consent_ip', 45)->nullable();
            $table->string('source', 100)->nullable();
            $table->dateTime('mailchimp_synced_at')->nullable();
            $table->timestamps();
        });

        Schema::create('expedition_subscriber', function (Blueprint $table): void {
            $table->foreignId('expedition_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subscriber_id')->constrained()->cascadeOnDelete();
            $table->primary(['expedition_id', 'subscriber_id']);
        });

        Schema::create('content_deliveries', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('post_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subscriber_id')->constrained()->cascadeOnDelete();
            $table->string('frequency', 20);
            $table->dateTime('sent_at')->nullable()->index();
            $table->string('status', 20)->default('queued')->index();
            $table->text('error')->nullable();
            $table->timestamps();
            $table->unique(['post_id', 'subscriber_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('content_deliveries');
        Schema::dropIfExists('expedition_subscriber');
        Schema::dropIfExists('subscribers');
        Schema::dropIfExists('expedition_registrations');
        Schema::dropIfExists('program_items');

        Schema::table('member_locations', function (Blueprint $table): void {
            $table->dropUnique(['expedition_id', 'user_id']);
            $table->dropConstrainedForeignId('expedition_id');
            $table->unique('user_id');
            $table->dropIndex('member_locations_user_id_index');
        });
        Schema::table('map_photos', fn (Blueprint $table) => $table->dropConstrainedForeignId('expedition_id'));
        Schema::table('expedition_states', function (Blueprint $table): void {
            $table->dropUnique(['expedition_id']);
            $table->dropConstrainedForeignId('expedition_id');
        });
        Schema::table('route_segments', fn (Blueprint $table) => $table->dropConstrainedForeignId('expedition_id'));
        Schema::table('route_points', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('location_id');
            $table->dropConstrainedForeignId('expedition_id');
        });
        Schema::table('posts', function (Blueprint $table): void {
            $table->dropIndex('posts_notification_frequency_index');
            $table->dropIndex('posts_notification_sent_at_index');
        });
        Schema::table('posts', function (Blueprint $table): void {
            $table->dropColumn(['notification_frequency', 'notification_sent_at']);
            $table->dropConstrainedForeignId('expedition_id');
        });

        Schema::dropIfExists('locations');
        Schema::dropIfExists('author_expedition');
        Schema::dropIfExists('expeditions');
    }
};
