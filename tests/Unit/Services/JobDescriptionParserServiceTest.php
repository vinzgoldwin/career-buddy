<?php

use App\Services\Job\JobDescriptionParserService;

it('parses a raw job description into structured data', function () {
    $raw = <<<'TXT'
About the job
About Accenture 

Accenture is a leading global professional services company that helps the world's leading businesses, governments and other organizations build their digital core, optimize their operations, accelerate revenue growth, and enhance citizen services - creating tangible value at speed and scale. We are a talent- and innovation-led company with approximately 774,000 people serving clients in more than 120 countries. Technology is at the core of change today, and we are one of the world's leaders in helping drive that change, with strong ecosystem relationships. We combine our strength in technology and leadership in cloud, data and AI with unmatched industry experience, functional expertise, and global delivery capability. We are uniquely able to deliver tangible outcomes because of our broad range of services, solutions and assets across Strategy & Consulting, Technology, Operations, Industry X and Song. These capabilities, together with our culture of shared success and commitment to creating 360° value, enable us to help our clients reinvent and build trusted, lasting relationships. We measure our success by the 360° value we create for our clients, each other, our shareholders, partners and communities. Visit us at www.accenture.com.

Job Summary:

We are seeking a skilled and experienced Node.js Developer to join our dynamic team. The ideal candidate should have at least 3 years of experience in Node.js development, with expertise in NestJS and 2 years of experience in the banking sector. The candidate will work within an Agile software development lifecycle, collaborating with cross-functional teams to design, develop, and deploy secure and scalable applications.

Key Responsibilities:


Design, develop, and maintain backend services and APIs using Node.js and NestJS.
Collaborate with front-end developers, UX/UI designers, and other stakeholders to create high-quality software solutions.
Ensure application security, performance, and scalability in accordance with banking industry standards.
Develop and maintain technical documentation for software solutions.
Participate in Agile ceremonies such as sprint planning, daily stand-ups, and retrospectives.
Implement unit and integration tests to ensure software reliability.
Troubleshoot and debug applications to optimize performance.
Stay updated with the latest industry trends and technologies in Node.js, NestJS, and Agile methodologies.


Required Qualifications:


3+ years of experience in backend development using Node.js.
Expertise in NestJS, including experience in building scalable and maintainable applications.
Experience with Python (good to have)
Hands-on experience with RESTful APIs, GraphQL, and Microservices architecture.
Strong knowledge of Agile software development lifecycle (SDLC).
Experience working with databases such as PostgreSQL, MySQL, or MongoDB.
Experience in AWS
Familiarity with CI/CD pipelines, Docker, and cloud platforms (AWS, Azure, or GCP).
Proficiency in Git version control and collaborative development workflows.
Strong problem-solving and analytical skills.
Excellent communication and teamwork abilities.


You will also have opportunities to hone your functional skills and expertise in an area of specialization.  We offer a variety of formal and informal training programs at every level to help you acquire and build specialized skills faster. Learning takes place both on the job and through formal training conducted online, in the classroom, or in collaboration with teammates. The sheer variety of work we do, and the experience it offers, provide an unbeatable platform from which to build a career.

Accenture is an equal opportunities employer and welcomes applications from all sections of society and does not discriminate on grounds of race, religion or belief, ethnic or national origin, disability, age, citizenship, marital, domestic or civil partnership status, sexual orientation, gender identity, or any other basis as protected by applicable law.
TXT;

    $service = new JobDescriptionParserService();
    $data = $service->parse($raw);

    expect($data)
        ->toHaveKeys(['company', 'role', 'summary', 'responsibilities', 'qualifications', 'experience', 'technologies', 'raw'])
        ->and($data['company']['name'])->toBe('Accenture')
        ->and($data['company']['website'])->toContain('accenture.com')
        ->and($data['role']['title'])->toContain('Node.js Developer')
        ->and($data['qualifications']['required'])->toContain('3+ years of experience in backend development using Node.js')
        ->and($data['experience']['total_years_min'])->toBe(2) // Minimum is 2 years (banking sector experience)
        ->and($data['experience']['total_years_max'])->toBe(3) // Maximum is 3 years (main requirement)
        ->and($data['experience']['domain_experience'][0]['domain'])->toBe('banking')
        ->and($data['experience']['domain_experience'][0]['years'])->toBe(2)
        ->and($data['technologies']['frameworks'])->toContain('NestJS')
        ->and($data['technologies']['cloud'])->toContain('AWS');

    expect(count($data['responsibilities']))->toBeGreaterThan(5);
});

it('parses an Indonesian job description with microservices and banking focus', function () {
    $raw = <<<'TXT'
About the job
Job Description

Bertanggung jawab membangun dan mengelola sistem back-end yang mendukung layanan mobile banking, termasuk integrasi dengan core banking, sistem pembayaran, dan layanan pihak ketiga. Menjamin keamanan, skalabilitas, dan ketersediaan API serta layanan server-side. Berkolaborasi dengan tim front-end, QA, dan keamanan untuk memastikan performa aplikasi yang andal dan sesuai regulasi industri keuangan.


Kualifikasi

Pendidikan minimal S1/D4/Sederajat Teknik Informatika, Sistem Informasi, Ilmu Komputer atau bidang terkait. 
Pengalaman 2–4 tahun mengembangkan service untuk berbagai jenis layanan digital berbasis teknologi (digital banking, merupakan nilai tambah). Terlibat dalam perancangan, pengembangan, dan integrasi service API maupun microservices yang mendukung kebutuhan.
Pemahaman mendalam tentang Struktur Data, Basis Data, dan dasar-dasar ilmu komputer lainnya. 
Pengalaman tentang: Microservices architecture, Event Driven architecture, Test-Driven Development (TDD). 
Nilai tambah: menguasai tracing, profiling, dan secure coding standard. 
Tech Stack: 
Microservices: Java (Spring Boot), Python, Node.js 
Messaging: Kafka, RabbitMQ, Redis Pub/Sub 
Database: ORACLE / SQL, MongoDB, Redis 
Data Transfer: SFTP, Object Storage (AWS S3/ECS) 
DevOps: Docker, Kubernetes, GitLab, Jenkins
TXT;

    $service = new JobDescriptionParserService();
    $data = $service->parse($raw);

    expect($data)
        ->toHaveKeys(['company', 'role', 'summary', 'responsibilities', 'qualifications', 'experience', 'technologies', 'raw'])
        ->and($data['role']['title'])->toBeNull() // No explicit role title in this description
        ->and($data['experience']['total_years_min'])->toBe(2)
        ->and($data['experience']['total_years_max'])->toBe(4)
        ->and($data['technologies']['architecture'])->toContain('Microservices')
        ->and($data['technologies']['languages'])->toContain('Java')
        ->and($data['technologies']['languages'])->toContain('Python')
        ->and($data['technologies']['languages'])->toContain('Node.js')
        ->and($data['technologies']['tools'])->toContain('Docker')
        ->and($data['technologies']['databases'])->toContain('MongoDB')
        ->and($data['technologies']['cloud'])->toContain('AWS');

    expect(count($data['qualifications']['required']))->toBeGreaterThan(5);
    
    // In this job description, responsibilities are part of the job summary, not in a separate section
    expect($data['summary'])->toContain('Bertanggung jawab membangun');
});