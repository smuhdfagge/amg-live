<?php

/*
|--------------------------------------------------------------------------
| SWATCH organisation details
|--------------------------------------------------------------------------
|
| Facts used across the public website. Edit them here, or override the
| contact values from the .env file, and every page picks up the change.
|
*/

return [

    'name' => 'Support for Women and Teenage Children',
    'short_name' => 'SWATCH',
    'founded' => 2000,
    'tagline' => 'A women-led organisation working with women, girls and children in Kano since 2000.',

    'registration' => [
        'cac' => 'CAC/IT/No.20797',
        'tin' => '22453598-0001',
    ],

    'contact' => [
        'address_line_1' => 'Plot 203, New Court Road',
        'address_line_2' => 'Gyadi-Gyadi, Kano',
        'country' => 'Nigeria',
        'phone' => env('SWATCH_PHONE', '0803 787 3565'),
        'phone_link' => env('SWATCH_PHONE_LINK', '+2348037873565'),
        'email' => env('SWATCH_EMAIL', 'swatchngo2002@gmail.com'),
        'email_alt' => env('SWATCH_EMAIL_ALT', 'ramatutuwa@gmail.com'),
        'hours' => 'Monday to Friday, 8:00am to 4:00pm',
        'map_query' => 'New Court Road, Gyadi-Gyadi, Kano, Nigeria',
    ],

    // Where website form notifications are sent.
    'notify_email' => env('SWATCH_NOTIFY_EMAIL', 'swatchngo2002@gmail.com'),

    // Social profiles. Leave a value empty and its icon is hidden.
    'social' => [
        'facebook' => env('SWATCH_FACEBOOK', ''),
        'x' => env('SWATCH_X', ''),
        'instagram' => env('SWATCH_INSTAGRAM', ''),
        'linkedin' => env('SWATCH_LINKEDIN', ''),
        'youtube' => env('SWATCH_YOUTUBE', ''),
    ],

    // Bank details for direct gifts. Shown on the Support page only when
    // the account number is filled in.
    'bank' => [
        'account_name' => env('SWATCH_BANK_ACCOUNT_NAME', ''),
        'account_number' => env('SWATCH_BANK_ACCOUNT_NUMBER', ''),
        'bank_name' => env('SWATCH_BANK_NAME', ''),
    ],

    'team' => [
        'trustees' => 5,
        'senior_management' => 5,
        'permanent_staff' => 9,
        'temporary_staff' => 3,
        'volunteers' => 80,
    ],

    'vision' => 'To see a healthy, empowered and poverty free society.',

    'mission' => 'To improve the health and socio-economic status of women, children and young people, so they can enjoy a good quality of life and become self-reliant, dignified members of their communities.',

    'skills' => [
        'Community mobilisation and empowerment',
        'Project management',
        'Advocacy',
        'Financial management',
        'Research',
        'Monitoring and evaluation',
        'Financial education training',
        'Database management',
    ],

    'methods' => [
        ['title' => 'Advocacy', 'text' => 'We press for policies and services that work for women, girls and children, and we bring community voices to the people who make decisions.'],
        ['title' => 'Community mobilisation', 'text' => 'Ward and community structures, volunteers and caregivers are at the centre of how we plan and deliver every programme.'],
        ['title' => 'Programme management', 'text' => 'We manage donor-funded programmes to agreed targets, budgets and reporting standards.'],
        ['title' => 'Research and evidence', 'text' => 'We collect and use data to decide where to work and to show what has changed. We took part in the national AIDS survey, NAIIS.'],
        ['title' => 'Capacity building', 'text' => 'We train caregivers, volunteers, community groups and our own staff so skills stay in the community after a project ends.'],
        ['title' => 'Outreach and monitoring', 'text' => 'Our teams go house to house and facility to facility, then follow up so that referrals turn into services received.'],
    ],

    'thematic_areas' => [
        [
            'slug' => 'health',
            'title' => 'Health',
            'icon' => 'health',
            'summary' => 'Fair access to good health care, with a long record in HIV prevention, testing, care and support.',
            'body' => 'Health has been the core of our work for more than two decades. We have delivered HIV awareness and prevention, HIV testing services, referral and linkage to treatment, and care and support for families affected by HIV. We see health as complete physical, mental and social wellbeing, not only the absence of illness.',
        ],
        [
            'slug' => 'orphans-and-vulnerable-children',
            'title' => 'Orphans and vulnerable children',
            'icon' => 'child',
            'summary' => 'Care, protection and support for children made vulnerable by HIV, poverty and violence, and for the households that look after them.',
            'body' => 'Through programmes with CEDPA, FHI 360, HIFASS and the Society for Family Health, we have reached tens of thousands of orphans, vulnerable children and caregivers in Kano with health, education, nutrition, protection, psychosocial and household economic support.',
        ],
        [
            'slug' => 'education',
            'title' => 'Education',
            'icon' => 'education',
            'summary' => 'Getting girls into school and keeping them there until they finish.',
            'body' => 'SWATCH was formed partly in response to low enrolment of girls, poor transition and completion rates and the falling quality of education. We support children to enrol and stay in school, and we work with parents and communities on the value of educating the girl-child.',
        ],
        [
            'slug' => 'nutrition-and-food-security',
            'title' => 'Nutrition and food security',
            'icon' => 'nutrition',
            'summary' => 'Safe, nutritious and sufficient food for every household, to end hunger and malnutrition.',
            'body' => 'We help households understand and meet the nutrition needs of young children, pregnant women and nursing mothers, and we link families to services that address hunger and malnutrition.',
        ],
        [
            'slug' => 'gender-and-social-inclusion',
            'title' => 'Gender and social inclusion',
            'icon' => 'inclusion',
            'summary' => 'Equal participation for women and marginalised groups in every part of community life.',
            'body' => 'We are a gender-sensitive organisation. We mainstream gender and social inclusion in all our work so that no one is left out of community participation, and our equality policy applies to how we recruit, keep, train and develop our own staff.',
        ],
        [
            'slug' => 'economic-empowerment',
            'title' => 'Economic empowerment',
            'icon' => 'livelihood',
            'summary' => 'Skills, savings and enterprise for women and young people, so families can stand on their own.',
            'body' => 'Extreme poverty among women and the lack of business skills among vulnerable people are among the reasons SWATCH exists. We provide financial education, entrepreneurship training and household economic support to caregivers, women and young people.',
        ],
        [
            'slug' => 'agriculture-and-climate',
            'title' => 'Agriculture and climate change',
            'icon' => 'agriculture',
            'summary' => 'Better farming practice that raises yields while reducing harm from a changing climate.',
            'body' => 'We support sustainable farming that increases productivity for rural households, especially those headed by women, while helping communities adapt to climate change.',
        ],
        [
            'slug' => 'wash',
            'title' => 'Water, sanitation and hygiene',
            'icon' => 'wash',
            'summary' => 'Clean water, decent sanitation and good hygiene practice for every household.',
            'body' => 'We promote safe water use, sanitation and hygiene in homes, schools and communities, and encourage practices that communities can keep up on their own.',
        ],
        [
            'slug' => 'human-rights',
            'title' => 'Human rights and protection',
            'icon' => 'rights',
            'summary' => 'Upholding the rights and dignity of every person through advocacy, awareness and accountability.',
            'body' => 'We work to protect women and children from abuse, exploitation and gender-based violence, and we support survivors to reach the services they need.',
        ],
        [
            'slug' => 'peace-and-governance',
            'title' => 'Peace building and good governance',
            'icon' => 'governance',
            'summary' => 'Dialogue, social cohesion and citizen participation in transparent, accountable government.',
            'body' => 'We bring communities together to prevent and resolve conflict, and we encourage women and young people to take part in decisions that affect them.',
        ],
    ],

];
