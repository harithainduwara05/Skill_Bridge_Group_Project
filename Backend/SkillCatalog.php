<?php
/**
 * Master list of recognised skills / technologies.
 * Single source of truth for validating the "Required Skills / Technologies"
 * field of internships (server side AND browser side).
 *
 * To allow a new skill: add it to skillCatalogNames() (or skillCatalogAliases()
 * for an alternative spelling). Nothing else needs to change.
 */

function skillCatalogNames()
{
    return [
        // Languages
        'PHP','JavaScript','TypeScript','Python','Java','C','C++','C#','Go','Rust','Kotlin','Swift','Dart','Ruby',
        'Scala','R','MATLAB','Perl','Lua','Haskell','Elixir','Objective-C','Assembly','Bash','PowerShell','SQL',
        'PL/SQL','T-SQL','HTML','CSS','SASS','SCSS','LESS','Solidity','Julia','Groovy','VB.NET','COBOL','Fortran',
        // Frontend
        'React','Angular','Vue.js','Svelte','Next.js','Nuxt.js','Redux','jQuery','Bootstrap','Tailwind CSS',
        'Material UI','Ant Design','Vite','Webpack','Gatsby','Alpine.js','Three.js','D3.js','Chart.js','WebAssembly',
        'Responsive Design','Accessibility','PWA',
        // Backend
        'Node.js','Express.js','NestJS','Laravel','Symfony','CodeIgniter','Django','Flask','FastAPI','Spring',
        'Spring Boot','ASP.NET','.NET','.NET Core','Ruby on Rails','Gin','Phoenix','Hibernate','GraphQL','REST API',
        'gRPC','WebSocket','Microservices','JWT','OAuth','Socket.io','RabbitMQ','Kafka','Apache',
        'Nginx',
        // Mobile
        'Android','iOS','Flutter','React Native','Xamarin','Ionic','SwiftUI','Jetpack Compose','Android Studio',
        'Xcode',
        // Databases
        'MySQL','PostgreSQL','MongoDB','SQLite','Oracle','SQL Server','MariaDB','Redis','Firebase','Firestore',
        'DynamoDB','Cassandra','Elasticsearch','Neo4j','Supabase','NoSQL','Database Design','Prisma','Sequelize',
        'Eloquent',
        // Cloud & DevOps
        'AWS','Azure','Google Cloud','Docker','Kubernetes','Terraform','Ansible','Jenkins','GitHub Actions',
        'GitLab CI','CI/CD','Linux','Unix','Git','GitHub','GitLab','Bitbucket','Heroku','Vercel','Netlify',
        'DigitalOcean','Cloudflare','Prometheus','Grafana','Vagrant','DevOps','Serverless','Lambda',
        // AI / Data
        'Machine Learning','Deep Learning','Artificial Intelligence','NLP','Computer Vision','TensorFlow','PyTorch',
        'Keras','scikit-learn','Pandas','NumPy','Matplotlib','OpenCV','Hugging Face','LangChain','Data Science',
        'Data Analysis','Data Engineering','Data Visualization','Big Data','Hadoop','Spark','Power BI','Tableau',
        'Excel','Google Sheets','ETL','Jupyter','Statistics','Generative AI','Prompt Engineering','LLM','Looker',
        // Design / UX
        'Figma','Adobe XD','Sketch','Photoshop','Illustrator','InDesign','Canva','UI Design','UX Design',
        'UI/UX','Wireframing','Prototyping','Graphic Design','Video Editing','Premiere Pro','After Effects',
        'Blender','Unity','Unreal Engine','Game Development','3D Modeling',
        // Testing / QA
        'Selenium','Cypress','Jest','Mocha','JUnit','PHPUnit','Postman','Playwright','Pytest','Unit Testing',
        'Manual Testing','Automation Testing','Test Automation','QA','Load Testing','JMeter','Appium',
        // Security / Networking
        'Cybersecurity','Ethical Hacking','Penetration Testing','Network Security','Cryptography','Networking',
        'Wireshark','Kali Linux','Cisco','TCP/IP','Firewalls','SIEM','VPN',
        // Practices / Tools
        'Agile','Scrum','Kanban','Jira','Trello','Confluence','Notion','Slack','Software Architecture',
        'Design Patterns','OOP','Data Structures','Algorithms','System Design','API Development',
        'Web Development','Full Stack Development','Frontend Development','Backend Development',
        'Mobile Development','Software Engineering','Software Testing','Version Control','WordPress','Shopify',
        'Magento','Drupal','Joomla','Odoo','SAP','Salesforce','ERP','CRM','IoT','Arduino','Raspberry Pi',
        'Embedded Systems','Blockchain','Ethereum','Web3','Microsoft Office','Word','PowerPoint','Outlook',
        'SEO','Digital Marketing','Content Writing','Technical Writing','Business Analysis','Requirements Analysis',
        'Project Management','Product Management','UML','ER Diagrams','Swagger','OpenAPI','Composer','npm',
        'Yarn','Maven','Gradle','XML','JSON','YAML','AJAX','Markdown','LaTeX',
        // Business / soft skills
        'Communication','Teamwork','Leadership','Problem Solving','Critical Thinking','Time Management',
        'Presentation Skills','Creativity','Adaptability','Collaboration','Analytical Skills','Research',
        'Customer Service','Public Speaking','Negotiation','Accounting','Marketing','Sales','Finance',
        'Social Media','Copywriting',
    ];
}

function skillCatalogAliases()
{
    // alternative spelling => canonical name (must exist in the list above)
    return [
        'js' => 'JavaScript', 'ecmascript' => 'JavaScript', 'es6' => 'JavaScript', 'ts' => 'TypeScript',
        'py' => 'Python', 'golang' => 'Go', 'cpp' => 'C++', 'csharp' => 'C#', 'cs' => 'C#',
        'reactjs' => 'React', 'react.js' => 'React', 'vue' => 'Vue.js', 'vuejs' => 'Vue.js',
        'angularjs' => 'Angular', 'nextjs' => 'Next.js', 'nuxtjs' => 'Nuxt.js', 'node' => 'Node.js',
        'nodejs' => 'Node.js', 'express' => 'Express.js', 'expressjs' => 'Express.js', 'nest' => 'NestJS',
        'rails' => 'Ruby on Rails', 'ror' => 'Ruby on Rails', 'dotnet' => '.NET', 'asp.net core' => '.NET Core',
        'springboot' => 'Spring Boot', 'tailwind' => 'Tailwind CSS', 'tailwindcss' => 'Tailwind CSS',
        'postgres' => 'PostgreSQL', 'postgresql' => 'PostgreSQL', 'mongo' => 'MongoDB', 'mssql' => 'SQL Server',
        'microsoft sql server' => 'SQL Server', 'k8s' => 'Kubernetes', 'gcp' => 'Google Cloud',
        'amazon web services' => 'AWS', 'ml' => 'Machine Learning', 'dl' => 'Deep Learning',
        'ai' => 'Artificial Intelligence', 'sklearn' => 'scikit-learn', 'scikit learn' => 'scikit-learn',
        'ui ux' => 'UI/UX', 'ux/ui' => 'UI/UX', 'rest' => 'REST API', 'restful api' => 'REST API',
        'restful' => 'REST API', 'api' => 'API Development', 'cicd' => 'CI/CD', 'object oriented programming' => 'OOP',
        'ms excel' => 'Excel', 'microsoft excel' => 'Excel', 'ms word' => 'Word', 'ms office' => 'Microsoft Office',
        'powerbi' => 'Power BI', 'html5' => 'HTML', 'css3' => 'CSS', 'tailwind css 3' => 'Tailwind CSS',
        'gitlab ci/cd' => 'GitLab CI', 'dsa' => 'Data Structures', 'ds' => 'Data Structures',
        'react-native' => 'React Native', 'adobe photoshop' => 'Photoshop', 'adobe illustrator' => 'Illustrator',
        'problem-solving' => 'Problem Solving', 'team work' => 'Teamwork', 'team-work' => 'Teamwork',
    ];
}

/** Lower-case and drop spaces . - _ so "Node.js", "node js", "NodeJS" compare equal. */
function skillKey($value)
{
    return preg_replace('/[\s.\-_]+/u', '', mb_strtolower(trim((string) $value)));
}

/** normalisedKey => canonical name, for both the names and the aliases. */
function skillCatalogMap()
{
    static $map = null;
    if ($map !== null) return $map;
    $map = [];
    foreach (skillCatalogNames() as $name) {
        $map[skillKey($name)] = $name;
    }
    foreach (skillCatalogAliases() as $alias => $canonical) {
        $map[skillKey($alias)] = $canonical;
    }
    return $map;
}

/** Closest canonical name for a typo, or '' when nothing is close enough. */
function skillSuggestion($input)
{
    $key = skillKey($input);
    if ($key === '') return '';
    $best = '';
    $bestScore = PHP_INT_MAX;
    foreach (skillCatalogMap() as $k => $canonical) {
        $d = levenshtein($key, $k);
        if ($d < $bestScore) { $bestScore = $d; $best = $canonical; }
    }
    $limit = max(1, (int) floor(strlen($key) / 3));
    return $bestScore <= $limit ? $best : '';
}
