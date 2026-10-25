<?php

namespace Database\Seeders;

use App\Models\Project;
use Illuminate\Database\Seeder;

class ProjectSeeder extends Seeder
{
    public function run(): void
    {
        $projects = [
            [
                'slug' => 'sharp-task-order-3',
                'title' => 'Strategic HIV and AIDS Response Program, Task Order 3',
                'short_title' => 'SHARP Task Order 3',
                'partner' => 'Chemonics International',
                'partner_role' => 'Implementing partner',
                'period' => 'Most recent',
                'start_year' => null,
                'end_year' => null,
                'status' => 'recent',
                'location' => 'Kano State',
                'theme' => 'HIV prevention',
                'is_featured' => true,
                'sort_order' => 1,
                'summary' => 'An HIV prevention programme run with Chemonics International to raise awareness, encourage safer behaviour, expand HIV testing and make sure people who test positive are linked to treatment.',
                'body' => "SHARP Task Order 3 is our most recent programme. We work with Chemonics International on HIV prevention in Kano, with a focus on reaching people early and keeping them connected to care.\n\n## What we do\n\n- Raise awareness and knowledge of HIV and AIDS in communities\n- Promote healthy behaviour, including risk avoidance\n- Provide HIV counselling and testing services\n- Strengthen referrals and linkage to care and treatment\n\nEach of these activities feeds into the wider SHARP objectives for the state.",
            ],
            [
                'slug' => 'ichssa-3',
                'title' => 'Integrated Child Health and Social Services Award 3',
                'short_title' => 'ICHSSA 3',
                'partner' => 'Society for Family Health (SFH)',
                'partner_role' => 'Implementing partner',
                'period' => '2020',
                'start_year' => 2020,
                'end_year' => 2020,
                'status' => 'completed',
                'location' => 'Kano State',
                'theme' => 'Orphans and vulnerable children',
                'is_featured' => true,
                'sort_order' => 2,
                'summary' => 'With the Society for Family Health, we worked to reduce the impact of HIV and AIDS on orphans and vulnerable children and to support HIV epidemic control.',
                'body' => "ICHSSA 3 set out to reduce the impact of HIV and AIDS on orphans and vulnerable children and, in doing so, to help bring the epidemic under control. The project made sure that these children were cared for and protected by their households, their communities, and their local and state governments.\n\n## Who the project enrolled\n\n- Children living with HIV\n- Children of people living with HIV\n- HIV-exposed infants\n- Children of key populations\n- Adolescents at risk\n- Survivors of gender-based violence",
            ],
            [
                'slug' => 'lopin-3',
                'title' => 'Local Orphans and Vulnerable Children Partners in Nigeria, Region 3',
                'short_title' => 'LOPIN-3',
                'partner' => 'Health Initiatives for Safety and Stability in Africa (HIFASS)',
                'partner_role' => 'Funding partner',
                'period' => '2019',
                'start_year' => 2019,
                'end_year' => 2019,
                'status' => 'completed',
                'location' => '2 LGAs, Kano State',
                'theme' => 'Orphans and vulnerable children',
                'reach_figure' => '12,083',
                'reach_label' => 'children and caregivers served',
                'is_featured' => true,
                'sort_order' => 3,
                'summary' => 'A community care, support and protection project for orphans and vulnerable children in two local government areas of Kano, funded by HIFASS.',
                'body' => "LOPIN-3 was a community-based care, support and protection project for orphans and vulnerable children (OVC) in Kano. With funding from Health Initiatives for Safety and Stability in Africa (HIFASS), SWATCH was one of the partners that delivered it.\n\nThe aim was to improve the quality of life of orphans and vulnerable children across two local government areas. We enrolled and served 12,083 children and their caregivers with a full package of services for each child and household.",
            ],
            [
                'slug' => 'naiis-survey',
                'title' => 'Nigeria AIDS Indicator and Impact Survey',
                'short_title' => 'NAIIS',
                'partner' => null,
                'partner_role' => 'Participating NGO',
                'period' => '2018 to 2019',
                'start_year' => 2018,
                'end_year' => 2019,
                'status' => 'completed',
                'location' => 'Kano and neighbouring states',
                'theme' => 'Research and HIV services',
                'reach_figure' => '22',
                'reach_label' => 'health facilities worked with',
                'is_featured' => false,
                'sort_order' => 4,
                'summary' => 'SWATCH was one of the NGOs that took part in the Nigeria AIDS Indicator and Impact Survey in Kano, tracking and referring people living with HIV into care.',
                'body' => "NAIIS was the national survey that measured HIV prevalence and the reach of HIV services across Nigeria. SWATCH was one of the NGOs that took part when the survey came to Kano.\n\nWe worked with about 22 health facilities inside and outside Kano. Our role was to track people who needed follow-up and to refer them for confirmatory HIV testing and for care and support services.",
            ],
            [
                'slug' => 'sidhas',
                'title' => 'Strengthening Integrated Delivery of HIV/AIDS Services',
                'short_title' => 'SIDHAS',
                'partner' => 'FHI 360',
                'partner_role' => 'Implementing partner',
                'period' => '2014 to 2018',
                'start_year' => 2014,
                'end_year' => 2018,
                'status' => 'completed',
                'location' => '9 LGAs, Kano State',
                'theme' => 'Orphans and vulnerable children',
                'reach_figure' => '6,420',
                'reach_label' => 'orphans and vulnerable children supported',
                'is_featured' => false,
                'sort_order' => 5,
                'summary' => 'Community HIV prevention and mitigation services across nine local government areas, delivered with FHI 360.',
                'body' => "SIDHAS brought medical and social services together. With FHI 360, we mobilised local communities to build their own lasting responses to support orphans and vulnerable children and to prevent HIV.\n\nWe delivered the project in nine local government areas of Kano. In total, 6,420 orphans and vulnerable children received care and support through the 6+1 OVC service model, which covers health, nutrition, education, protection, psychosocial support, shelter and household economic strengthening.",
            ],
            [
                'slug' => 'cedpa-care-and-support',
                'title' => 'Expanded Compassionate Gender-Sensitive Care and Support',
                'short_title' => 'Care and Support for OVC',
                'partner' => 'Centre for Development and Population Activities (CEDPA)',
                'partner_role' => 'Implementing partner',
                'period' => '2007 to 2010',
                'start_year' => 2007,
                'end_year' => 2010,
                'status' => 'completed',
                'location' => '3 LGAs, Kano State',
                'theme' => 'Orphans and vulnerable children',
                'reach_figure' => '10,000+',
                'reach_label' => 'orphans and vulnerable children reached',
                'is_featured' => false,
                'sort_order' => 6,
                'summary' => 'Care and support for people living with and affected by HIV and AIDS, and for orphans and vulnerable children, delivered with CEDPA.',
                'body' => "Working with the Centre for Development and Population Activities (CEDPA), SWATCH delivered gender-sensitive care and support for people living with and affected by HIV and AIDS, alongside care and support for orphans and vulnerable children.\n\nThe project reached more than 10,000 orphans and vulnerable children in three local government areas of Kano.",
            ],
        ];

        foreach ($projects as $project) {
            Project::updateOrCreate(['slug' => $project['slug']], $project);
        }
    }
}
