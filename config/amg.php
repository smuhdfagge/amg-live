<?php

/*
|--------------------------------------------------------------------------
| Aminu Magashi Garba Foundation details
|--------------------------------------------------------------------------
|
| Facts used across the public website. Edit them here, or override the
| contact values from the .env file, and every page picks up the change.
| Leave a value empty and the part of the site that shows it is hidden.
|
*/

return [

    'name' => 'Aminu Magashi Garba Foundation',
    'short_name' => 'AMG Foundation',
    'founded' => env('AMG_FOUNDED', ''),
    'tagline' => 'Improving the lives of vulnerable people and communities in Kano and beyond.',

    'registration' => [
        'cac' => env('AMG_CAC', ''),
        'tin' => env('AMG_TIN', ''),
    ],

    'contact' => [
        'address_line_1' => 'No. 9 Kura Road, off Lamido Crescent',
        'address_line_2' => 'Nassarawa GRA, Kano',
        'country' => 'Nigeria',
        'phone' => env('AMG_PHONE', ''),
        'phone_link' => env('AMG_PHONE_LINK', ''),
        'email' => env('AMG_EMAIL', ''),
        'email_alt' => env('AMG_EMAIL_ALT', ''),
        'hours' => env('AMG_HOURS', ''),
        'map_query' => '9 Kura Road, Nassarawa GRA, Kano, Nigeria',
    ],

    // Where website form notifications are sent.
    'notify_email' => env('AMG_NOTIFY_EMAIL', ''),

    // Social profiles. Leave a value empty and its icon is hidden.
    'social' => [
        'facebook' => env('AMG_FACEBOOK', ''),
        'x' => env('AMG_X', ''),
        'instagram' => env('AMG_INSTAGRAM', ''),
        'linkedin' => env('AMG_LINKEDIN', ''),
        'youtube' => env('AMG_YOUTUBE', ''),
    ],

    // Largest audio and video files the admin can upload, in megabytes.
    // The server's own PHP limits (upload_max_filesize, post_max_size) must
    // be at least this big too; the admin form shows the lower of the two.
    'uploads' => [
        'audio_mb' => (int) env('AMG_MAX_AUDIO_MB', 50),
        'video_mb' => (int) env('AMG_MAX_VIDEO_MB', 200),
    ],

    // Bank details for direct gifts. Shown on the Support page only when
    // the account number is filled in.
    'bank' => [
        'account_name' => env('AMG_BANK_ACCOUNT_NAME', ''),
        'account_number' => env('AMG_BANK_ACCOUNT_NUMBER', ''),
        'bank_name' => env('AMG_BANK_NAME', ''),
    ],

    'vision' => 'A society well equipped and responding adequately and timely to development and humanitarian challenges.',

    'mission' => 'Improving the lives of the vulnerable people and communities through lasting multi-stakeholder and multi-sectoral partnerships, and ensuring sustainable financial mechanisms and innovative solutions to the many problems of women, youths, children, the elderly, adolescents and people with disability.',

    'objectives' => [
        'Empower the most vulnerable people in society, such as women, youths, children, the elderly, adolescents and people with disability, through innovative financing and solutions.',
        'Ease the suffering of people living with health challenges and disabilities, such as sickle cell disease and other blood disorders, mental health problems, cancers and physical disabilities.',
    ],

    // The groups named in the mission. Shown on the home and about pages.
    'groups' => ['Women', 'Youths', 'Children', 'The elderly', 'Adolescents', 'People with disability'],

    'methods' => [
        ['title' => 'Lasting partnerships', 'text' => 'We work with government, development partners, the private sector, community leaders and other civil society groups, because no single organisation can solve these problems alone.'],
        ['title' => 'Many sectors, one goal', 'text' => 'Health, livelihoods, education and social protection affect each other. We plan across sectors so that support for a family meets more than one need.'],
        ['title' => 'Sustainable financing', 'text' => 'We look for funding arrangements that keep support going after the first grant ends, so that families are not left stranded.'],
        ['title' => 'Innovative solutions', 'text' => 'We test new ideas, new tools and new ways of reaching people, and we keep the ones that work.'],
        ['title' => 'Timely response', 'text' => 'When a crisis hits a community, speed matters. We aim to be ready to respond early and adequately to humanitarian needs.'],
        ['title' => 'Accountability', 'text' => 'We keep clear records of what we spend and what changes, and we report back to the people and partners who support us.'],
    ],

    'thematic_areas' => [
        [
            'slug' => 'sickle-cell-and-blood-disorders',
            'title' => 'Sickle cell and blood disorders',
            'icon' => 'health',
            'summary' => 'Support for people living with sickle cell disease and other blood disorders, and for the families who care for them.',
            'body' => 'Sickle cell disease and other blood disorders bring repeated pain, hospital stays and costs that many families in our communities cannot carry alone. We work to ease that burden by helping patients reach care, raising awareness in communities, and working with partners to make treatment and support easier to get.',
        ],
        [
            'slug' => 'mental-health',
            'title' => 'Mental health',
            'icon' => 'inclusion',
            'summary' => 'Care, understanding and support for people facing mental health problems.',
            'body' => 'Mental illness is often hidden, misunderstood or met with stigma. We want people with mental health problems and their families to be treated with dignity, to know where to find help, and to be able to get it. We work with partners and communities to raise awareness and to link people to care.',
        ],
        [
            'slug' => 'cancer-support',
            'title' => 'Cancer support',
            'icon' => 'rights',
            'summary' => 'Easing the suffering of people living with cancer and helping families cope.',
            'body' => 'A cancer diagnosis can drain a family of money and hope. We aim to ease the suffering of patients by supporting access to care, encouraging early detection, and standing with families through treatment.',
        ],
        [
            'slug' => 'disability-inclusion',
            'title' => 'Disability inclusion',
            'icon' => 'hand',
            'summary' => 'Equal opportunity, dignity and support for people with physical and other disabilities.',
            'body' => 'People with disability are among the most likely to be left out of school, work and community life. We support people with physical and other disabilities to take part fully, and we push for services and spaces that include them.',
        ],
        [
            'slug' => 'women-and-girls',
            'title' => 'Women and girls',
            'icon' => 'users',
            'summary' => 'Opening up opportunity for women and girls through skills, finance and support.',
            'body' => 'When women and girls do well, their families and communities do well too. We work to empower women and girls with skills, access to finance and the support they need to make their own choices and improve their lives.',
        ],
        [
            'slug' => 'youths-and-adolescents',
            'title' => 'Youths and adolescents',
            'icon' => 'education',
            'summary' => 'Helping young people build the skills, health and confidence to take charge of their future.',
            'body' => 'Young people make up a large share of our communities. We want adolescents and youths to have the knowledge, skills and opportunities that lead to good health, decent work and active citizenship.',
        ],
        [
            'slug' => 'children',
            'title' => 'Children',
            'icon' => 'child',
            'summary' => 'Protecting children and giving them a healthy, safe start in life.',
            'body' => 'Children in poor and vulnerable households face the greatest risks to their health, safety and schooling. We work with families and communities so that children are cared for, protected and able to grow and learn.',
        ],
        [
            'slug' => 'the-elderly',
            'title' => 'The elderly',
            'icon' => 'home',
            'summary' => 'Care and dignity for older people, who are too often forgotten.',
            'body' => 'Older people carry a lifetime of experience, yet many live with poor health, low income and little support. We work to make sure the elderly are cared for, respected and included in the life of their communities.',
        ],
        [
            'slug' => 'innovative-financing',
            'title' => 'Innovative financing',
            'icon' => 'livelihood',
            'summary' => 'New ways of funding support so that it reaches more people and lasts longer.',
            'body' => 'Short-term handouts rarely change lives for good. We look for sustainable financial mechanisms, from partnerships with the private sector to community-based schemes, that put lasting resources in the hands of vulnerable people.',
        ],
        [
            'slug' => 'humanitarian-response',
            'title' => 'Humanitarian response',
            'icon' => 'gift',
            'summary' => 'Responding early and adequately when communities face a crisis.',
            'body' => 'Floods, displacement, disease outbreaks and other emergencies hit the most vulnerable hardest. Our vision is a society that responds adequately and on time to development and humanitarian challenges, and we work with partners to help communities prepare, respond and recover.',
        ],
    ],

];
