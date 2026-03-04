<?php

namespace Database\Seeders;

use App\Models\Agency;
use App\Models\Concept;
use App\Models\ConceptTask;
use App\Models\EditTask;
use App\Models\Lead;
use App\Models\PanelNotification;
use App\Models\Project;
use App\Models\ShootConceptLink;
use App\Models\ShootSchedule;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DummyDataSeeder extends Seeder
{
    public function run(): void
    {
        // ── 0. Create Agencies ──────────────────────────────────
        $agencies = [
            Agency::create(['name' => 'Digital Yug', 'owner_name' => 'Ravi Patel', 'contact' => '9876543210', 'remark' => 'Main Agency']),
            Agency::create(['name' => 'Creative Ads', 'owner_name' => 'Suresh Raina', 'contact' => '9812345678', 'remark' => 'Partner for social media ads']),
            Agency::create(['name' => 'Blue Sky Marketing', 'owner_name' => 'Vijay Singh', 'contact' => '9834567890', 'remark' => 'Specializes in real estate marketing']),
        ];

        // ── 1. Create Users ────────────────────────────────────
        $admin = User::create([
            'name' => 'Ravi Patel',
            'email' => 'admin@digitalyug.com',
            'password' => Hash::make('password'),
            'phone' => '9876543210',
            'is_active' => true,
        ]);
        $admin->assignRole('Admin');

        $manager = User::create([
            'name' => 'Neha Sharma',
            'email' => 'manager@digitalyug.com',
            'password' => Hash::make('password'),
            'phone' => '9876543211',
            'is_active' => true,
        ]);
        $manager->assignRole('Manager');

        $sales1 = User::create([
            'name' => 'Arjun Mehta',
            'email' => 'sales@digitalyug.com',
            'password' => Hash::make('password'),
            'phone' => '9876543212',
            'is_active' => true,
        ]);
        $sales1->assignRole('Sales Executive');

        $sales2 = User::create([
            'name' => 'Priya Desai',
            'email' => 'sales2@digitalyug.com',
            'password' => Hash::make('password'),
            'phone' => '9876543213',
            'is_active' => true,
        ]);
        $sales2->assignRole('Sales Executive');

        $writer = User::create([
            'name' => 'Ankit Joshi',
            'email' => 'concept@digitalyug.com',
            'password' => Hash::make('password'),
            'phone' => '9876543214',
            'is_active' => true,
        ]);
        $writer->assignRole('Concept Writer');

        $writer2 = User::create([
            'name' => 'Kavya Nair',
            'email' => 'concept2@digitalyug.com',
            'password' => Hash::make('password'),
            'phone' => '9876543215',
            'is_active' => true,
        ]);
        $writer2->assignRole('Concept Writer');

        $shooter = User::create([
            'name' => 'Rohan Verma',
            'email' => 'shoot@digitalyug.com',
            'password' => Hash::make('password'),
            'phone' => '9876543216',
            'is_active' => true,
        ]);
        $shooter->assignRole('Shooting Person');

        $shooter2 = User::create([
            'name' => 'Aditya Kumar',
            'email' => 'shoot2@digitalyug.com',
            'password' => Hash::make('password'),
            'phone' => '9876543217',
            'is_active' => true,
        ]);
        $shooter2->assignRole('Shooting Person');

        $editor = User::create([
            'name' => 'Simran Kaur',
            'email' => 'editor@digitalyug.com',
            'password' => Hash::make('password'),
            'phone' => '9876543218',
            'is_active' => true,
        ]);
        $editor->assignRole('Video Editor');

        $editor2 = User::create([
            'name' => 'Mohit Gupta',
            'email' => 'editor2@digitalyug.com',
            'password' => Hash::make('password'),
            'phone' => '9876543219',
            'is_active' => true,
        ]);
        $editor2->assignRole('Video Editor');

        // ── 2. Create Leads ────────────────────────────────────
        $leadsData = [
            [
                'customer_name' => 'Bodhi Wellness Spa',
                'contact_number' => '9823456789',
                'date' => '2026-01-05',
                'total_reels' => 12,
                'total_posts' => 8,
                'total_meta_budget' => 30000,
                'client_meta_budget' => 18000,
                'dy_meta_budget' => 12000,
                'status' => 'converted',
                'notes' => 'Premium spa brand, focuses on organic lifestyle.',
                'created_by' => $sales1->id,
            ],
            [
                'customer_name' => 'Aura Fashion House',
                'contact_number' => '9812345678',
                'date' => '2026-01-10',
                'total_reels' => 20,
                'total_posts' => 15,
                'total_meta_budget' => 50000,
                'client_meta_budget' => 30000,
                'dy_meta_budget' => 20000,
                'status' => 'converted',
                'notes' => 'Fashion brand targeting 18-35 age group.',
                'created_by' => $sales1->id,
            ],
            [
                'customer_name' => 'Horizon Real Estate',
                'contact_number' => '9834567890',
                'date' => '2026-01-15',
                'total_reels' => 8,
                'total_posts' => 12,
                'total_meta_budget' => 80000,
                'client_meta_budget' => 50000,
                'dy_meta_budget' => 30000,
                'status' => 'converted',
                'notes' => 'Real estate developer with new township launch.',
                'created_by' => $sales2->id,
            ],
            [
                'customer_name' => 'ZenFit Gym',
                'contact_number' => '9845678901',
                'date' => '2026-01-20',
                'total_reels' => 16,
                'total_posts' => 10,
                'total_meta_budget' => 25000,
                'client_meta_budget' => 15000,
                'dy_meta_budget' => 10000,
                'status' => 'confirmed',
                'notes' => 'New gym branch opening, needs brand awareness.',
                'created_by' => $sales1->id,
            ],
            [
                'customer_name' => 'TasteBud Restaurant',
                'contact_number' => '9856789012',
                'date' => '2026-01-25',
                'total_reels' => 10,
                'total_posts' => 20,
                'total_meta_budget' => 20000,
                'client_meta_budget' => 12000,
                'dy_meta_budget' => 8000,
                'status' => 'converted',
                'notes' => 'Multi-cuisine restaurant, wants food reel content.',
                'created_by' => $sales2->id,
            ],
            [
                'customer_name' => 'CloudNine Clinic',
                'contact_number' => '9867890123',
                'date' => '2026-02-01',
                'total_reels' => 8,
                'total_posts' => 12,
                'total_meta_budget' => 35000,
                'client_meta_budget' => 22000,
                'dy_meta_budget' => 13000,
                'status' => 'contacted',
                'notes' => 'Skincare clinic, wants before/after content.',
                'created_by' => $sales1->id,
            ],
            [
                'customer_name' => 'Velocity Motors',
                'contact_number' => '9878901234',
                'date' => '2026-02-05',
                'total_reels' => 6,
                'total_posts' => 8,
                'total_meta_budget' => 60000,
                'client_meta_budget' => 40000,
                'dy_meta_budget' => 20000,
                'status' => 'new',
                'notes' => 'Two-wheeler dealership launch campaign.',
                'created_by' => $sales2->id,
            ],
            [
                'customer_name' => 'Bloom Bakery',
                'contact_number' => '9889012345',
                'date' => '2026-02-10',
                'total_reels' => 14,
                'total_posts' => 18,
                'total_meta_budget' => 15000,
                'client_meta_budget' => 9000,
                'dy_meta_budget' => 6000,
                'status' => 'new',
                'notes' => 'Artisan bakery wanting lifestyle content.',
                'created_by' => $sales1->id,
            ],
            [
                'customer_name' => 'Sunrise Edu Hub',
                'contact_number' => '9890123456',
                'date' => '2026-02-15',
                'total_reels' => 10,
                'total_posts' => 15,
                'total_meta_budget' => 40000,
                'client_meta_budget' => 25000,
                'dy_meta_budget' => 15000,
                'status' => 'lost',
                'notes' => 'Education startup, budget mismatch.',
                'created_by' => $sales2->id,
            ],
            [
                'customer_name' => 'Prestige Jewellers',
                'contact_number' => '9901234567',
                'date' => '2026-02-20',
                'total_reels' => 18,
                'total_posts' => 12,
                'total_meta_budget' => 70000,
                'client_meta_budget' => 45000,
                'dy_meta_budget' => 25000,
                'status' => 'contacted',
                'notes' => 'Gold and diamond jewellery brand, festive season.',
                'created_by' => $sales1->id,
            ],
        ];

        $leads = [];
        foreach ($leadsData as $i => $data) {
            $agency = $agencies[$i % count($agencies)];
            $leads[] = Lead::create(array_merge($data, [
                'agency_name' => $agency->name,
                'agency_id' => $agency->id
            ]));
        }

        // ── 3. Create Projects from Converted Leads ────────────
        $convertedLeads = array_filter($leads, fn($l) => $l->status === 'converted');
        $convertedLeads = array_values($convertedLeads);

        // Project 1: Bodhi Wellness – COMPLETED
        $p1 = Project::create([
            'lead_id' => $convertedLeads[0]->id,
            'name' => 'Bodhi Wellness Spa – Jan 2026',
            'start_date' => '2026-01-08',
            'end_date' => '2026-02-08',
            'stage' => 'completed',
            'total_concepts' => 12,
            'approved_concepts' => 10,
            'total_shoots' => 5,
            'completed_shoots' => 5,
            'total_edits' => 10,
            'completed_edits' => 10,
            'manager_id' => $manager->id,
        ]);

        // Project 2: Aura Fashion – EDITING stage
        $p2 = Project::create([
            'lead_id' => $convertedLeads[1]->id,
            'name' => 'Aura Fashion House – Jan 2026',
            'start_date' => '2026-01-12',
            'end_date' => '2026-02-12',
            'stage' => 'editing',
            'total_concepts' => 20,
            'approved_concepts' => 16,
            'total_shoots' => 8,
            'completed_shoots' => 8,
            'total_edits' => 16,
            'completed_edits' => 9,
            'manager_id' => $manager->id,
        ]);

        // Project 3: Horizon Real Estate – SHOOTING stage
        $p3 = Project::create([
            'lead_id' => $convertedLeads[2]->id,
            'name' => 'Horizon Real Estate – Jan 2026',
            'start_date' => '2026-01-18',
            'end_date' => '2026-02-18',
            'stage' => 'shooting',
            'total_concepts' => 8,
            'approved_concepts' => 6,
            'total_shoots' => 3,
            'completed_shoots' => 1,
            'total_edits' => 0,
            'completed_edits' => 0,
            'manager_id' => $manager->id,
        ]);

        // Project 4: TasteBud Restaurant – CONCEPT stage
        $p4 = Project::create([
            'lead_id' => $convertedLeads[3]->id,
            'name' => 'TasteBud Restaurant – Jan 2026',
            'start_date' => '2026-01-27',
            'end_date' => '2026-02-27',
            'stage' => 'concept',
            'total_concepts' => 10,
            'approved_concepts' => 3,
            'total_shoots' => 0,
            'completed_shoots' => 0,
            'total_edits' => 0,
            'completed_edits' => 0,
            'manager_id' => $manager->id,
        ]);

        // ── 4. Create Concept Tasks & Concepts ─────────────────
        // P4: TasteBud – Active concept writing
        $ct1 = ConceptTask::create([
            'project_id' => $p4->id,
            'assigned_to' => $writer->id,
            'assigned_by' => $manager->id,
            'concepts_required' => 10,
            'general_remarks' => 'Focus on food aesthetics and dining experience. Include both product shots and lifestyle shots. Use warm colour tones.',
            'status' => 'in_progress',
            'due_date' => '2026-02-05',
        ]);

        $conceptsData = [
            ['title' => 'Sunday Brunch Vibes', 'description' => 'Show the restaurant\'s popular Sunday brunch spread with family gathering. Feature pancakes, eggs benedict, fresh juices.', 'client_allocation' => 'Reel 1', 'remarks' => 'Must include family of 4', 'status' => 'approved'],
            ['title' => 'Chef\'s Special Preparation', 'description' => 'Behind-the-scenes reel of the head chef preparing the signature dish. Close-up shots of cooking techniques.', 'client_allocation' => 'Reel 2', 'remarks' => 'Show kitchen hygiene', 'status' => 'approved'],
            ['title' => 'Romantic Candlelight Dinner', 'description' => 'Couple dining experience in the premium section with ambient lighting. Feature signature cocktails.', 'client_allocation' => 'Reel 3', 'remarks' => 'Evening shoot needed', 'status' => 'approved'],
            ['title' => 'Fresh Ingredients Story', 'description' => 'Morning delivery of fresh vegetables and spices. Farm-to-table narrative.', 'client_allocation' => 'Reel 4', 'remarks' => 'Early morning shoot 6 AM', 'status' => 'client_review'],
            ['title' => 'Dessert Showcase', 'description' => 'Slow-motion reel of dessert plating. Chocolate lava cake, tiramisu, etc.', 'client_allocation' => 'Reel 5', 'remarks' => 'Use macro lens shots', 'status' => 'client_review'],
            ['title' => 'Group Celebrations', 'description' => 'Birthday and corporate party setups. Show decoration, cake cutting, group dining.', 'client_allocation' => 'Post 1-3', 'remarks' => 'Static posts for 3 events', 'status' => 'draft'],
            ['title' => 'Signature Drinks Reel', 'description' => 'Bartender making signature cocktails and mocktails in style.', 'client_allocation' => 'Reel 6', 'remarks' => 'Include 5 different drinks', 'status' => 'draft'],
            ['title' => 'Kids Menu Highlights', 'description' => 'Fun family-friendly content showing kids enjoying meals.', 'client_allocation' => 'Post 4-6', 'remarks' => 'Need 2 child actors', 'status' => 'draft'],
            ['title' => 'Happy Hour Specials', 'description' => 'Evening happy hour deals promotion reel.', 'client_allocation' => 'Reel 7', 'remarks' => 'Include price tags', 'status' => 'draft'],
            ['title' => 'Restaurant Ambiance Tour', 'description' => 'Complete tour of the restaurant space showing different sections.', 'client_allocation' => 'Reel 8', 'remarks' => 'Drone + handheld mix', 'status' => 'draft'],
        ];

        foreach ($conceptsData as $i => $cd) {
            Concept::create([
                'concept_task_id' => $ct1->id,
                'project_id' => $p4->id,
                'title' => $cd['title'],
                'description' => $cd['description'],
                'client_allocation' => $cd['client_allocation'],
                'remarks' => $cd['remarks'],
                'writer_notes' => 'Researched similar campaigns. Ready for execution.',
                'status' => $cd['status'],
                'sequence' => $i + 1,
            ]);
        }

        // P3: Horizon Real Estate – concepts mostly done
        $ct2 = ConceptTask::create([
            'project_id' => $p3->id,
            'assigned_to' => $writer2->id,
            'assigned_by' => $manager->id,
            'concepts_required' => 8,
            'general_remarks' => 'Luxury real estate feel. Show lifestyle, amenities, and location advantages.',
            'status' => 'completed',
            'due_date' => '2026-01-25',
        ]);

        $realEstateConcepts = [
            ['title' => 'Sunrise Township Overview', 'description' => 'Aerial drone shots of the entire township at sunrise.', 'status' => 'approved'],
            ['title' => 'Luxury Villa Interior', 'description' => 'Walk-through of a model 3BHK villa with premium interiors.', 'status' => 'approved'],
            ['title' => 'Amenities Showcase', 'description' => 'Swimming pool, gym, clubhouse, and children\'s play area.', 'status' => 'approved'],
            ['title' => 'Green Living Concept', 'description' => 'Emphasize eco-friendly design, rainwater harvesting, solar panels.', 'status' => 'approved'],
            ['title' => 'Location Connectivity', 'description' => 'Show proximity to school, hospital, mall, and highway.', 'status' => 'approved'],
            ['title' => 'Happy Homeowners Story', 'description' => 'Testimonial-style reel with satisfied residents.', 'status' => 'approved'],
            ['title' => 'Construction Quality', 'description' => 'Behind-the-scenes of construction quality checks.', 'status' => 'rejected'],
            ['title' => 'Festive Offer Promotion', 'description' => 'Special discount offer announcement reel.', 'status' => 'approved'],
        ];

        foreach ($realEstateConcepts as $i => $rc) {
            Concept::create([
                'concept_task_id' => $ct2->id,
                'project_id' => $p3->id,
                'title' => $rc['title'],
                'description' => $rc['description'],
                'client_allocation' => 'Reel/Post ' . ($i + 1),
                'remarks' => 'High production quality required.',
                'writer_notes' => 'Concepts finalized after site visit.',
                'status' => $rc['status'],
                'sequence' => $i + 1,
            ]);
        }

        // ── 5. Create Shoot Schedules ──────────────────────────
        // P3: Horizon Real Estate – partially shot
        $shoot1 = ShootSchedule::create([
            'project_id' => $p3->id,
            'location' => 'Horizon Township, Sola Road, Ahmedabad',
            'shoot_date' => '2026-02-05',
            'planned_start_time' => '07:00:00',
            'checkin_at' => '2026-02-05 07:15:00',
            'checkout_at' => '2026-02-05 14:30:00',
            'shooting_person_id' => $shooter->id,
            'model_name' => 'Meera Shah',
            'concept_writer_id' => $writer2->id,
            'helper_name' => 'Rahul Singh',
            'reels_shot' => 3,
            'notes' => 'First shoot completed. Drone shots were excellent. Good lighting from 8-11 AM.',
            'status' => 'completed',
            'created_by' => $manager->id,
        ]);

        $shoot2 = ShootSchedule::create([
            'project_id' => $p3->id,
            'location' => 'Horizon Clubhouse, Sola Road, Ahmedabad',
            'shoot_date' => '2026-02-15',
            'planned_start_time' => '09:00:00',
            'checkin_at' => '2026-02-15 09:10:00',
            'checkout_at' => null,
            'shooting_person_id' => $shooter->id,
            'model_name' => 'Meera Shah',
            'concept_writer_id' => $writer2->id,
            'helper_name' => 'Rahul Singh',
            'reels_shot' => 2,
            'notes' => 'Amenities shoot in progress.',
            'status' => 'in_progress',
            'created_by' => $manager->id,
        ]);

        // P2: Aura Fashion – all shoots done
        $shoot3 = ShootSchedule::create([
            'project_id' => $p2->id,
            'location' => 'Studio Click, CG Road, Ahmedabad',
            'shoot_date' => '2026-01-20',
            'planned_start_time' => '10:00:00',
            'checkin_at' => '2026-01-20 10:05:00',
            'checkout_at' => '2026-01-20 18:00:00',
            'shooting_person_id' => $shooter2->id,
            'model_name' => 'Zara Khan',
            'concept_writer_id' => $writer->id,
            'helper_name' => 'Pooja Rao',
            'reels_shot' => 8,
            'notes' => 'Summer collection shoot. All 8 looks completed.',
            'status' => 'completed',
            'created_by' => $manager->id,
        ]);

        // ── 6. Create Edit Tasks ───────────────────────────────
        // P2: Aura Fashion – editing in progress
        $editTasksData = [
            [
                'project_id' => $p2->id,
                'assigned_to' => $editor->id,
                'title' => 'Aura Summer – Reel Batch 1 (Concepts 1-5)',
                'description' => 'Edit 5 fashion reels with trending audio. Use fast cuts. Color grade: warm and vibrant.',
                'total_videos' => 5,
                'completed_count' => 5,
                'status' => 'approved',
                'approval_notes' => 'Excellent work! Client loved the color grading.',
                'approved_at' => '2026-02-10 15:00:00',
                'approved_by' => $manager->id,
            ],
            [
                'project_id' => $p2->id,
                'assigned_to' => $editor->id,
                'title' => 'Aura Summer – Reel Batch 2 (Concepts 6-10)',
                'description' => 'Edit 5 more fashion reels. Match style with Batch 1.',
                'total_videos' => 5,
                'completed_count' => 4,
                'status' => 'in_progress',
                'approval_notes' => null,
                'approved_at' => null,
                'approved_by' => null,
            ],
            [
                'project_id' => $p2->id,
                'assigned_to' => $editor2->id,
                'title' => 'Aura Summer – Static Posts (6 Posts)',
                'description' => 'Design 6 static posts for Instagram feed. Use brand colors.',
                'total_videos' => 6,
                'completed_count' => 6,
                'status' => 'review',
                'approval_notes' => null,
                'approved_at' => null,
                'approved_by' => null,
            ],
        ];

        foreach ($editTasksData as $et) {
            EditTask::create(array_merge($et, ['assigned_by' => $manager->id]));
        }

        // P1: Bodhi Wellness – completed
        EditTask::create([
            'project_id' => $p1->id,
            'assigned_to' => $editor->id,
            'assigned_by' => $manager->id,
            'title' => 'Bodhi Wellness – Complete Campaign',
            'description' => 'Full campaign edit: 10 reels + 8 posts.',
            'total_videos' => 18,
            'completed_count' => 18,
            'status' => 'approved',
            'approval_notes' => 'Campaign delivered on time. Client very satisfied.',
            'approved_at' => '2026-02-08 17:00:00',
            'approved_by' => $manager->id,
        ]);

        // ── 7. Create Panel Notifications ─────────────────────
        $notifs = [
            ['user_id' => $writer->id, 'triggered_by' => $manager->id, 'type' => 'concept_assigned', 'title' => 'New Concept Task Assigned', 'message' => 'You have been assigned to write 10 concepts for TasteBud Restaurant project.', 'link' => '/concepts', 'is_read' => false],
            ['user_id' => $shooter->id, 'triggered_by' => $manager->id, 'type' => 'shoot_scheduled', 'title' => 'Shoot Schedule Updated', 'message' => 'Your shoot at Horizon Clubhouse on Feb 15 is now in progress.', 'link' => '/shoots', 'is_read' => false],
            ['user_id' => $editor->id, 'triggered_by' => $manager->id, 'type' => 'edit_assigned', 'title' => 'New Editing Task Assigned', 'message' => 'Edit Batch 2 for Aura Fashion House has been assigned to you.', 'link' => '/editing', 'is_read' => false],
            ['user_id' => $editor2->id, 'triggered_by' => $manager->id, 'type' => 'edit_assigned', 'title' => 'Editing Task – Review Required', 'message' => 'Your static posts for Aura Summer are under review by manager.', 'link' => '/editing', 'is_read' => true],
            ['user_id' => $sales1->id, 'triggered_by' => $manager->id, 'type' => 'lead_updated', 'title' => 'Lead Status Updated', 'message' => 'Bodhi Wellness Spa lead has been converted to a project successfully.', 'link' => '/leads', 'is_read' => true],
            ['user_id' => $manager->id, 'triggered_by' => $writer->id, 'type' => 'concepts_submitted', 'title' => 'Concepts Submitted for Review', 'message' => 'Ankit Joshi submitted 3 concepts from TasteBud project for client review.', 'link' => '/concepts', 'is_read' => false],
            ['user_id' => $admin->id, 'triggered_by' => $manager->id, 'type' => 'project_completed', 'title' => 'Project Marked Complete', 'message' => 'Bodhi Wellness Spa Jan 2026 campaign has been completed.', 'link' => '/projects', 'is_read' => true],
            ['user_id' => $writer2->id, 'triggered_by' => $manager->id, 'type' => 'concept_approved', 'title' => '6 Concepts Approved', 'message' => 'Manager approved 6 concepts for Horizon Real Estate project. Shoot scheduling will begin.', 'link' => '/concepts', 'is_read' => true],
            ['user_id' => $shooter2->id, 'triggered_by' => $manager->id, 'type' => 'shoot_scheduled', 'title' => 'New Shoot Scheduled', 'message' => 'You are assigned to Aura Fashion Studio shoot on Jan 20.', 'link' => '/shoots', 'is_read' => true],
            ['user_id' => $editor->id, 'triggered_by' => $manager->id, 'type' => 'edit_approved', 'title' => 'Editing Approved!', 'message' => 'Aura Summer Batch 1 has been approved by manager. Great work!', 'link' => '/editing', 'is_read' => false],
        ];

        foreach ($notifs as $n) {
            PanelNotification::create(array_merge($n, [
                'notifiable_type' => null,
                'notifiable_id' => null,
            ]));
        }

        // ── 8. Link Concepts to Shoots (Shoot-Concept Correlations) ──
        // Link P3 concepts to Shoot 1
        $p3Concepts = Concept::where('project_id', $p3->id)->get();
        foreach ($p3Concepts->take(3) as $c) {
            ShootConceptLink::create([
                'shoot_schedule_id' => $shoot1->id,
                'concept_id' => $c->id,
                'is_shot' => ($c->status === 'approved')
            ]);
        }

        // Link P3 concepts to Shoot 2
        foreach ($p3Concepts->skip(3)->take(2) as $c) {
            ShootConceptLink::create([
                'shoot_schedule_id' => $shoot2->id,
                'concept_id' => $c->id,
                'is_shot' => false
            ]);
        }

        // Link P2 concepts to Shoot 3
        $p2Concepts = Concept::where('project_id', $p2->id)->get();
        foreach ($p2Concepts as $c) {
            ShootConceptLink::create([
                'shoot_schedule_id' => $shoot3->id,
                'concept_id' => $c->id,
                'is_shot' => true
            ]);
        }
    }
}
