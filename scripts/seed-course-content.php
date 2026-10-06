<?php
/**
 * Add meaningful section names and starter lesson pages to the demo courses.
 *
 * The script is idempotent: curated names and previously created demo pages
 * are updated consistently, while unrelated course activities are preserved.
 */

define('CLI_SCRIPT', true);

$moodleroot = is_file(__DIR__ . '/../config.php') ? dirname(__DIR__) : '/var/www/html';
require($moodleroot . '/config.php');
require_once($CFG->dirroot . '/course/lib.php');
require_once($CFG->libdir . '/resourcelib.php');
require_once($CFG->dirroot . '/mod/resource/lib.php');
require_once($CFG->libdir . '/questionlib.php');
require_once($CFG->dirroot . '/mod/quiz/locallib.php');

global $DB, $USER;

// The Moodle module API performs capability checks. Run this maintenance
// script as the configured site administrator without creating a session.
$USER = $DB->get_record('user', ['username' => $CFG->admin], '*', MUST_EXIST);

function lesson(string $title, string $focus, string $activity, string $question, bool $answer = true): array {
    return compact('title', 'focus', 'activity', 'question', 'answer');
}

// Each item is a real lesson rather than a repeated placeholder. The stable
// titles and idnumbers let later production migrations update the curriculum
// without duplicating activities or losing learner progress.
$curriculum = [
    'TECH-JS' => [
        lesson('JavaScript Foundations', 'How JavaScript executes in the browser, how scripts are loaded, and how the console helps diagnose a program.', 'Create a small page that prints a welcome message and inspect the result in browser developer tools.', 'The browser console can be used to inspect JavaScript values while a page is running.', true),
        lesson('Variables and Data Types', 'const and let, primitive values, type coercion, template literals, and choosing clear names for application state.', 'Build a profile object and display a formatted summary while converting one user input from text to a number.', 'const prevents reassignment of the binding, although an object declared with const can still be mutated.', true),
        lesson('Control Flow and Functions', 'Conditions, loops, function parameters, return values, scope, and small reusable validation functions.', 'Write a reusable function that validates a course registration form and returns a useful error message.', 'A return statement ends the current function call and can provide a value to its caller.', true),
        lesson('Arrays and Objects', 'Array methods, nested data, object properties, destructuring, and transforming records into display-ready data.', 'Model a list of courses, filter by category, and map the result into cards for the catalogue.', 'map creates a new array by transforming each item in the source array.', true),
        lesson('DOM and Browser Events', 'Selecting elements, changing content safely, event listeners, form events, and accessible interaction patterns.', 'Create a searchable course list that updates as the user types without reloading the page.', 'An event listener allows code to respond when a user interacts with an element.', true),
        lesson('Asynchronous JavaScript', 'Promises, async and await, loading states, and handling work that finishes after the current call stack.', 'Add a loading indicator around a simulated request and display a friendly message when it fails.', 'An async function always returns a promise, even when it returns a regular value.', true),
        lesson('APIs and Error Handling', 'HTTP requests, JSON, response validation, try/catch, and separating network errors from invalid data.', 'Build a small API client that handles a successful response, a non-200 response, and invalid JSON.', 'A successful HTTP response does not guarantee that the response body contains valid application data.', true),
        lesson('Project: Interactive Web App', 'Planning, component boundaries, state updates, accessibility, and testing the complete interaction flow.', 'Design and implement a course dashboard with search, filters, empty states, and a saved selection.', 'A useful front-end project should define its user flow and error states before the final visual polish.', true),
    ],
    'TECH-PHP' => [
        lesson('PHP Foundations', 'PHP request execution, scalar types, arrays, operators, and the difference between server-side rendering and browser code.', 'Create a PHP page that renders a course greeting from a small associative array.', 'PHP code runs on the server before the generated HTML is sent to the browser.', true),
        lesson('Forms and Validation', 'Reading POST data, validating required fields, escaping output, and returning useful form feedback.', 'Build a course enquiry form that validates an email address and redisplays safe user input after an error.', 'User input should be validated for meaning and escaped for the output context.', true),
        lesson('Functions and Arrays', 'Reusable functions, callbacks, associative arrays, sorting, filtering, and keeping business rules testable.', 'Write a function that filters courses by language and returns a predictable result for an empty list.', 'An associative array stores values under named keys rather than only numeric positions.', true),
        lesson('Object-Oriented PHP', 'Classes, constructors, visibility, interfaces, and using objects to model a course catalogue.', 'Create a Course value object and a catalogue service that returns only published courses.', 'Private properties can only be changed through methods exposed by their owning class.', true),
        lesson('MySQL and PDO', 'Parameterized queries, transactions, indexes, and mapping database records into application data.', 'Implement a repository method that searches courses with PDO placeholders instead of string concatenation.', 'Prepared statements help separate SQL structure from user-supplied values.', true),
        lesson('Sessions and Authentication', 'Sessions, password hashing, authorization checks, and protecting pages that require an enrolled learner.', 'Add a login flow that stores only a user identifier in the session and checks a course capability.', 'password_hash stores a one-way password representation suitable for later verification.', true),
        lesson('Building APIs', 'Routing, JSON responses, status codes, input validation, and consistent error envelopes.', 'Design a course progress endpoint with validation for the course id and a clear not-found response.', 'An API should communicate success and failure through both its response body and HTTP status code.', true),
        lesson('Project: CRUD Web App', 'Combining forms, persistence, validation, authentication, and clean separation into a maintainable PHP application.', 'Plan and build a small course-note manager with create, list, edit, and delete flows.', 'A CRUD feature is complete only when validation, authorization, persistence, and user feedback work together.', true),
    ],
    'TECH-PYTHON' => [
        lesson('Python Foundations', 'Python syntax, names, values, control flow, virtual environments, and writing readable small programs.', 'Create a command-line course planner that accepts a learner name and prints a weekly study target.', 'Python uses indentation to define code blocks.', true),
        lesson('Collections and Comprehensions', 'Lists, tuples, dictionaries, sets, comprehensions, and selecting the right collection for the job.', 'Transform a list of course records into a dictionary keyed by course short name.', 'A dictionary is useful when values need to be retrieved by a meaningful key.', true),
        lesson('Functions and Modules', 'Function contracts, default arguments, imports, modules, and avoiding hidden global state.', 'Split a progress calculator into a reusable module with a small public API.', 'A default argument is evaluated when the function is defined, not each time it is called.', false),
        lesson('Files and Exceptions', 'Text and JSON files, context managers, exception types, and reliable cleanup of resources.', 'Export a learner study plan to JSON and handle a missing or malformed file gracefully.', 'A with statement helps ensure a file is closed when the block finishes.', true),
        lesson('Object-Oriented Python', 'Classes, dataclasses, composition, properties, and modeling course progress with focused objects.', 'Create a dataclass for a lesson and a progress object that reports completion percentage.', 'Composition can be used when one object should contain and coordinate other objects.', true),
        lesson('Testing and Debugging', 'Assertions, unit tests, fixtures, logging, tracebacks, and reducing a bug to a small example.', 'Write tests for progress calculations, including an empty course and a partially completed course.', 'A failing test is useful evidence about a behavior that needs investigation.', true),
        lesson('Automation and APIs', 'HTTP clients, parsing JSON, retries, environment variables, and small automation scripts.', 'Write a script that fetches a public JSON endpoint and stores a normalized course summary.', 'Secrets and environment-specific values should not be hard-coded in an automation script.', true),
        lesson('Project: Python Application', 'Project structure, configuration, testing, documentation, and delivering a small working application.', 'Build a command-line learning tracker with import, progress updates, and a weekly report.', 'A useful project includes a clear entry point, documented setup, and tests for its core rules.', true),
    ],
    'TECH-DOCKER' => [
        lesson('Containers and Images', 'Images, containers, layers, registries, and the difference between an immutable image and runtime state.', 'Run a web service, inspect its logs, and explain which data survives when the container is removed.', 'A container is a running process created from an image.', true),
        lesson('Writing Dockerfiles', 'Base images, copy order, working directories, ports, entrypoints, and repeatable builds.', 'Write a Dockerfile for a small application and arrange layers to improve rebuild speed.', 'Changing a layer invalidates that layer and the layers that follow it in the build.', true),
        lesson('Volumes and Persistent Data', 'Named volumes, bind mounts, backup considerations, and separating application code from database state.', 'Persist a database directory in a named volume and verify that data survives a container replacement.', 'A named volume is managed by Docker and is independent of an individual container lifecycle.', true),
        lesson('Container Networking', 'Networks, service discovery, published ports, internal ports, and least-privilege connectivity.', 'Connect an application container to a database by service name on a private Docker network.', 'Containers on the same user-defined network can resolve each other by service name.', true),
        lesson('Docker Compose', 'Multi-service definitions, dependencies, health checks, environment values, and local development workflows.', 'Define an application and database stack with a health check before the application starts.', 'depends_on alone does not always mean a dependency is ready to accept requests.', true),
        lesson('Environment Variables and Secrets', 'Configuration injection, secret handling, defaults, and avoiding credentials in images or Git.', 'Configure SMTP and database values through environment variables and document the required secrets.', 'A secret should be supplied at runtime rather than baked into a public image layer.', true),
        lesson('Security and Image Optimization', 'Non-root execution, minimal images, pinned versions, updates, scanning, and reducing attack surface.', 'Review an image for unnecessary packages and rewrite one layer to reduce the final size.', 'Removing build tools from the runtime image can reduce both size and attack surface.', true),
        lesson('Project: Deployable Application Stack', 'Designing a production-ready Compose deployment with persistence, health checks, logs, and safe updates.', 'Prepare a deployment checklist for an application, database, persistent volume, and reverse proxy.', 'A production deployment plan must include data persistence, rollback thinking, and operational checks.', true),
    ],
    'TECH-AI' => [
        lesson('AI and Machine Learning Concepts', 'Artificial intelligence, machine learning, training data, models, features, labels, and evaluation.', 'Classify a few everyday examples as rules-based automation, supervised learning, or generative AI.', 'A machine-learning model learns patterns from data rather than receiving every rule explicitly.', true),
        lesson('Data Preparation', 'Data quality, missing values, feature representation, leakage, splitting datasets, and reproducibility.', 'Create a small data checklist and identify one source of leakage in a sample prediction task.', 'Information from the future outcome must not leak into the features used to train a model.', true),
        lesson('Supervised Learning', 'Regression, classification, baselines, metrics, overfitting, and choosing a model for the task.', 'Compare a simple baseline with a classifier and explain which metric matters for the use case.', 'A baseline gives a reference point for deciding whether a more complex model is useful.', true),
        lesson('Neural Networks', 'Layers, weights, activations, loss, optimization, and why validation behavior matters.', 'Trace how one input moves through a small network and describe the role of a loss function.', 'Training adjusts model parameters to reduce a defined loss on the training examples.', true),
        lesson('Natural Language Processing', 'Tokens, embeddings, classification, retrieval, prompt design, and evaluating language-system output.', 'Design a small text classification workflow and list examples that could confuse the model.', 'Text systems can produce fluent output that is still factually or contextually wrong.', true),
        lesson('Computer Vision', 'Pixels, image labels, detection, augmentation, bias in datasets, and interpreting model confidence.', 'Review a simple image dataset and identify lighting, framing, and representation problems.', 'A high confidence score does not guarantee that a vision prediction is correct.', true),
        lesson('Responsible AI', 'Privacy, fairness, explainability, human oversight, safety, and documenting model limitations.', 'Write a short risk register for an AI feature used in an education platform.', 'Human review and clear escalation paths are important when an AI decision affects a learner.', true),
        lesson('Project: AI Workflow', 'From problem framing to data, baseline, evaluation, deployment, monitoring, and responsible iteration.', 'Create an end-to-end plan for a learner-support assistant with success and safety measures.', 'A responsible AI project defines both product success metrics and harm-prevention checks.', true),
    ],
    'ISLAMIC-QURAN' => [
        lesson('مقدمة في تدبر القرآن', 'معنى التدبر، آداب التلاوة، ومهارة القراءة التي تجمع بين الفهم والعمل.', 'اكتب هدفاً شخصياً للتدبر وسجّل سؤالين تريد الإجابة عنهما أثناء الدورة.', 'التدبر يتجاوز قراءة الألفاظ إلى فهم المعاني وربطها بالعمل.', true),
        lesson('سورة الفاتحة', 'المعاني المركزية في الفاتحة: الحمد، العبادة، الاستعانة، والهداية.', 'قسّم السورة إلى مقاطع معنوية واكتب أثراً عملياً لكل مقطع.', 'تجمع سورة الفاتحة بين الثناء على الله وطلب الهداية والاستعانة به.', true),
        lesson('فهم السياق والمعاني', 'أهمية السياق، ترابط الآيات، والتمييز بين المعنى العام والتفسير المتسرع.', 'اختر آيات قصيرة واقرأ ما قبلها وما بعدها ثم لخّص الرابط بينها.', 'يساعد سياق السورة على فهم المقصود من الآية فهماً أدق.', true),
        lesson('الهدايات العملية', 'تحويل المعنى القرآني إلى خلق وقرار وعادة يومية قابلة للمراجعة.', 'حوّل هداية واحدة إلى عادة أسبوعية واكتب مؤشراً بسيطاً لمتابعتها.', 'الهداية العملية تظهر عندما ينعكس فهم الآية على السلوك.', true),
        lesson('مشروع التدبر الأسبوعي', 'منهجية إعداد تأمل قصير موثق، مع تجنب الجزم بقول على الله بلا علم.', 'أنشئ بطاقة تدبر تتضمن الآيات، الفكرة الرئيسة، الفائدة، وخطوة عمل.', 'ينبغي أن يلتزم التأمل القرآني بالمصادر الموثوقة ويتجنب التفسير بلا علم.', true),
    ],
    'ISLAMIC-HADITH' => [
        lesson('مدخل إلى السنة النبوية', 'مكانة السنة، علاقتها بالقرآن، وأهمية التثبت قبل نسبة قول إلى النبي صلى الله عليه وسلم.', 'اكتب الفرق بين الحديث النبوي والحديث القدسي والخبر العام بأسلوبك.', 'السنة النبوية مصدر مهم لفهم الدين وتطبيقه مع القرآن الكريم.', true),
        lesson('أنواع الحديث ومصطلحاته', 'مفاهيم الصحيح والحسن والضعيف، الراوي، الإسناد، والمتن بصورة تمهيدية.', 'أنشئ قاموساً صغيراً من خمسة مصطلحات مع مثال أو تعريف مختصر لكل مصطلح.', 'يهتم علم مصطلح الحديث بمعرفة حال الراوي والمروي من حيث القبول والرد.', true),
        lesson('فهم الحديث وتوثيقه', 'قراءة النص في سياقه، الرجوع إلى مصادر التخريج، وعدم اقتطاع المعنى من سباقه.', 'اختر حديثاً من مصدر موثوق وسجّل المصدر والموضوع والفائدة دون نشر نص غير متحقق.', 'توثيق الحديث يتطلب الرجوع إلى مصدر معتبر قبل الاستدلال به.', true),
        lesson('تطبيق السنة في الحياة', 'ترجمة الهدي النبوي إلى سلوك في العبادة والمعاملة والأسرة والعمل.', 'اختر خلقاً نبوياً وصمّم ممارسة أسبوعية لملاحظته في موقفين يوميين.', 'المقصود من تعلم السنة أن تظهر آثارها في الفهم والعمل والأخلاق.', true),
        lesson('مشروع حديث عملي', 'إعداد بطاقة تعليمية لحديث واحد مع المصدر، المعنى، الفوائد، والتطبيق.', 'قدّم بطاقة حديث قصيرة وراجعها للتأكد من صحة المصدر ووضوح الفائدة.', 'البطاقة التعليمية الجيدة تذكر المصدر وتفصل بين نص الحديث والتأمل الشخصي.', true),
    ],
    'ISLAMIC-FIQH' => [
        lesson('مقدمة في الفقه والعبادة', 'معنى الفقه، مقاصد العبادة، وأهمية التعلم من مصادر أهل العلم الموثوقة.', 'اكتب خريطة للمجالات التي ستدرسها واربط كل مجال بعبادة يومية.', 'الفقه هو فهم الأحكام الشرعية العملية من أدلتها التفصيلية.', true),
        lesson('الطهارة', 'الوضوء والغسل والنظافة وأثر الطهارة في الاستعداد للصلاة مع مراعاة اختلاف الأحوال.', 'اكتب خطوات عملية للوضوء وحدد موضعاً تحتاج فيه إلى سؤال عالم موثوق.', 'الطهارة من الاستعدادات الأساسية للصلاة وليست بديلاً عن طلب العلم عند الإشكال.', true),
        lesson('الصلاة', 'أوقات الصلاة وأركانها وواجباتها والخشوع بوصفه حضوراً للقلب والعمل.', 'أنشئ قائمة مراجعة مختصرة للاستعداد للصلاة وسجّل وسيلة تساعدك على المحافظة عليها.', 'الصلاة عبادة لها أوقات وأركان وشروط ينبغي تعلمها من مصدر موثوق.', true),
        lesson('الصيام والزكاة', 'مقاصد الصيام، أحكام عامة للصائم، ومعنى الزكاة ودورها في التكافل.', 'قارن بين هدف الصيام وهدف الزكاة واكتب مثالاً على أثر كل عبادة في المجتمع.', 'الصيام والزكاة عبادتان لهما أحكام تفصيلية ينبغي أخذها من أهل العلم.', true),
        lesson('تطبيقات فقهية يومية', 'أدب السؤال، التعامل مع اختلاف الفتاوى، وتطبيق العلم في السفر والعمل والأسرة.', 'أنشئ سيناريوهين يوميين واكتب كيف تبحث عن الحكم فيهما دون إصدار فتوى من نفسك.', 'من الأمانة في الفقه أن يعرف المتعلم حدود علمه ويرجع إلى المختص عند الحاجة.', true),
    ],
    'ISLAMIC-SEERAH' => [
        lesson('مدخل إلى السيرة النبوية', 'لماذا ندرس السيرة، مصادرها، وكيف نقرأ الأحداث لفهم الهدي والقيم لا لمجرد سرد التاريخ.', 'اكتب ثلاثة أهداف شخصية من دراسة السيرة ومصدراً موثوقاً ستعود إليه.', 'دراسة السيرة تجمع بين معرفة الأحداث واستخلاص الهدي والقيم منها.', true),
        lesson('مكة قبل البعثة', 'المجتمع المكي، مكانة البيت، حياة النبي قبل البعثة، وبدايات الوحي.', 'رتّب خمس محطات زمنية في بطاقة واحدة واربط كل محطة بقيمة تعلمتها.', 'يساعد فهم البيئة المكية على قراءة أحداث الدعوة الأولى في سياقها.', true),
        lesson('الدعوة والهجرة', 'الصبر، التخطيط، الصحبة، وبذل الأسباب في مرحلة الدعوة والهجرة.', 'حلّل قراراً من أحداث الهجرة وحدد فيه السبب والتوكل والنتيجة.', 'التوكل لا يلغي الأخذ بالأسباب والتخطيط المسؤول.', true),
        lesson('بناء المجتمع في المدينة', 'المؤاخاة، الوثيقة، المسجد، والمسؤولية المشتركة في بناء مجتمع متماسك.', 'صمّم مبادرة صغيرة تخدم مجتمعك مستلهماً قيمة المؤاخاة والتعاون.', 'بناء المجتمع في السيرة قام على الإيمان والمسؤولية والتعاون.', true),
        lesson('دروس وقيم من السيرة', 'الرحمة، العدل، الشجاعة، الوفاء، وحسن القيادة في المواقف المختلفة.', 'اكتب موقفاً من السيرة وقيمة واحدة وخطوة عملية لتطبيقها هذا الأسبوع.', 'الهدف من السيرة أن تتحول القيم المستفادة إلى سلوك عملي.', true),
    ],
    'ISLAMIC-ETHICS' => [
        lesson('مفهوم الأخلاق في الإسلام', 'العلاقة بين الإيمان والخلق، النية، المسؤولية، ومراقبة أثر السلوك في الآخرين.', 'اكتب خلقين تريد تقويتهما وحدد موقفاً يومياً لملاحظتهما.', 'الأخلاق في الإسلام ترتبط بالنية وبالمعاملة وبالمسؤولية عن أثر العمل.', true),
        lesson('الصدق والأمانة', 'الصدق في القول والعمل، حفظ الحقوق، والفرق بين الأمانة والنتيجة السريعة.', 'صمّم موقفاً عملياً تظهر فيه الأمانة في الدراسة أو العمل أو التعامل الرقمي.', 'الأمانة تشمل حفظ الحقوق والالتزام بما اؤتمن عليه الإنسان.', true),
        lesson('الرحمة والتعاون', 'الرحمة بالناس، التعاون على الخير، والاستماع الذي يحفظ كرامة الطرف الآخر.', 'نفّذ عملاً تعاونياً صغيراً وسجّل كيف أثّر أسلوبك في النتيجة.', 'التعاون الأخلاقي يجمع بين نفع الآخرين واحترام كرامتهم.', true),
        lesson('ضبط النفس والمسؤولية', 'الصبر، إدارة الغضب، الاعتذار، وتحمل نتيجة القرار قبل لوم الآخرين.', 'استخدم قاعدة توقف قصيرة في موقف متوتر ثم اكتب ما تغير في ردك.', 'ضبط النفس لا يعني إلغاء المشاعر بل اختيار رد مسؤول عليها.', true),
        lesson('مشروع القيم في الحياة', 'تحويل القيم إلى ميثاق شخصي قابل للملاحظة في الأسرة والدراسة والعمل والمجتمع.', 'اكتب ميثاقاً من خمس جمل، واختر قيمة واحدة لتقييمها خلال أسبوع.', 'القيمة تصبح مؤثرة عندما تتحول إلى سلوك يمكن ملاحظته ومراجعته.', true),
    ],
];

$courses = [];
foreach ($curriculum as $shortname => $lessons) {
    $courses[$shortname] = array_column($lessons, 'title');
}

// Older production imports used these short names. Reuse those records when
// present so their learner enrolments and history are preserved while the
// curated content is upgraded in place.
$coursealiases = [
    'TECH-JS' => ['web-js-bootcamp'],
    'TECH-AI' => ['ai-practical'],
];

// Public introductory videos for every demo course. They are embedded in the
// first lesson page and also include a direct watch link for restricted
// networks or learners who prefer the YouTube player.
$videos = [
    'TECH-JS' => [
        'title' => 'JavaScript course for beginners',
        'embed' => 'https://www.youtube-nocookie.com/embed/W6NZfCO5SIk',
        'watch' => 'https://www.youtube.com/watch?v=W6NZfCO5SIk',
    ],
    'TECH-PHP' => [
        'title' => 'PHP programming language full course',
        'embed' => 'https://www.youtube-nocookie.com/embed/OK_JCtrrv-c',
        'watch' => 'https://www.youtube.com/watch?v=OK_JCtrrv-c',
    ],
    'TECH-PYTHON' => [
        'title' => 'Python full course for beginners',
        'embed' => 'https://www.youtube-nocookie.com/embed/rfscVS0vtbw',
        'watch' => 'https://www.youtube.com/watch?v=rfscVS0vtbw',
    ],
    'TECH-DOCKER' => [
        'title' => 'Docker tutorial for beginners',
        'embed' => 'https://www.youtube-nocookie.com/embed/fqMOX6JJhGo',
        'watch' => 'https://www.youtube.com/watch?v=fqMOX6JJhGo',
    ],
    'TECH-AI' => [
        'title' => 'What is a neural network?',
        'embed' => 'https://www.youtube-nocookie.com/embed/aircAruvnKk',
        'watch' => 'https://www.youtube.com/watch?v=aircAruvnKk',
    ],
    'ISLAMIC-QURAN' => [
        'title' => 'مقدمة في علوم القرآن مع تدبر سورة العاديات',
        'embed' => 'https://www.youtube-nocookie.com/embed/0IvGkFyGass',
        'watch' => 'https://www.youtube.com/watch?v=0IvGkFyGass',
    ],
    'ISLAMIC-HADITH' => [
        'title' => 'مقدمة في مصطلح الحديث',
        'embed' => 'https://www.youtube-nocookie.com/embed/K_aUON9VED4',
        'watch' => 'https://www.youtube.com/watch?v=K_aUON9VED4',
    ],
    'ISLAMIC-FIQH' => [
        'title' => 'مدخل إلى الفقه: الطهارة والصلاة',
        'embed' => 'https://www.youtube-nocookie.com/embed/PIsES6XGMZA',
        'watch' => 'https://www.youtube.com/watch?v=PIsES6XGMZA',
    ],
    'ISLAMIC-SEERAH' => [
        'title' => 'السيرة النبوية: الدرس الأول',
        'embed' => 'https://www.youtube-nocookie.com/embed/-u5_SGXm3ww',
        'watch' => 'https://www.youtube.com/watch?v=-u5_SGXm3ww',
    ],
    'ISLAMIC-ETHICS' => [
        'title' => 'قيم الإسلام: قيمة الرحمة',
        'embed' => 'https://www.youtube-nocookie.com/embed/2nseyrVhlpU',
        'watch' => 'https://www.youtube.com/watch?v=2nseyrVhlpU',
    ],
];

function demo_content(string $shortname, string $coursefullname, string $sectiontitle, array $lessondata = []): string {
    $course = htmlspecialchars($coursefullname, ENT_QUOTES, 'UTF-8');
    $title = htmlspecialchars($sectiontitle, ENT_QUOTES, 'UTF-8');
    $focus = htmlspecialchars((string)($lessondata['focus'] ?? ''), ENT_QUOTES, 'UTF-8');
    $activity = htmlspecialchars((string)($lessondata['activity'] ?? ''), ENT_QUOTES, 'UTF-8');
    $question = htmlspecialchars((string)($lessondata['question'] ?? ''), ENT_QUOTES, 'UTF-8');
    $arabic = strpos($shortname, 'ISLAMIC-') === 0;

    if ($arabic) {
        return "<h3>{$title}</h3>"
            . "<p><strong>الفكرة الرئيسة:</strong> {$focus}</p>"
            . '<h4>مخرجات التعلم</h4>'
            . '<ul><li>فهم المفاهيم الأساسية المرتبطة بالموضوع.</li>'
            . '<li>ربط المعرفة بالمواقف اليومية بصورة عملية.</li>'
            . '<li>كتابة تأمل قصير أو مثال تطبيقي بعد إكمال الدرس.</li></ul>'
            . '<h4>نشاط تطبيقي</h4>'
            . "<p>{$activity}</p>"
            . '<h4>تأمل سريع</h4>'
            . "<p>{$question}</p>"
            . "<p>بعد إكمال النشاط، شارك فائدة واحدة أو سؤالاً في منتدى دورة {$course}.</p>";
    }

    return "<h3>{$title}</h3>"
        . "<p><strong>Lesson focus:</strong> {$focus}</p>"
        . '<h4>Learning outcomes</h4>'
        . '<ul><li>Explain the key ideas covered in this section.</li>'
        . '<li>Apply the concepts in a small practical example.</li>'
        . '<li>Reflect on one question or challenge related to the lesson.</li></ul>'
        . '<h4>Practice activity</h4>'
        . "<p>{$activity}</p>"
        . '<h4>Quick reflection</h4>'
        . "<p>{$question}</p>"
        . "<p>When you finish, share one takeaway or question in the {$course} course forum.</p>";
}

function demo_video_content(string $shortname, array $video): string {
    $title = htmlspecialchars($video['title'], ENT_QUOTES, 'UTF-8');
    $embed = htmlspecialchars($video['embed'], ENT_QUOTES, 'UTF-8');
    $watch = htmlspecialchars($video['watch'], ENT_QUOTES, 'UTF-8');
    $arabic = strpos($shortname, 'ISLAMIC-') === 0;
    $intro = $arabic
        ? 'شاهد هذا الدرس التمهيدي قبل دراسة أقسام الدورة.'
        : 'Watch this introductory lesson before working through the course sections.';
    $watchlabel = $arabic ? 'فتح الفيديو على يوتيوب' : 'Open this video on YouTube';
    $reflection = $arabic
        ? 'اكتب فائدتين من الفيديو تريد تطبيقهما أثناء دراسة هذه الدورة.'
        : 'Write down two ideas from the video that you want to practise in this course.';
    $reflectionlabel = $arabic ? 'تأمل:' : 'Reflection:';

    return '<h3>' . $title . '</h3>'
        . '<p>' . $intro . '</p>'
        . '<div style="position:relative;padding-bottom:56.25%;height:0;overflow:hidden;max-width:100%;">'
        . '<iframe src="' . $embed . '" title="' . $title . '" '
        . 'style="position:absolute;top:0;left:0;width:100%;height:100%;border:0;" '
        . 'allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" '
        . 'allowfullscreen></iframe></div>'
        . '<p><a href="' . $watch . '" target="_blank" rel="noopener">' . $watchlabel . '</a></p>'
        . '<p><strong>' . $reflectionlabel . '</strong> ' . $reflection . '</p>';
}

function pdf_ascii(string $text): string {
    $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $converted = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text);
    if ($converted !== false) {
        $text = $converted;
    }
    return preg_replace('/[^\x20-\x7E]/', '', $text);
}

function pdf_escape(string $text): string {
    return str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $text);
}

function demo_pdf(string $shortname, string $coursefullname, array $sectiontitles): string {
    $lines = [
        'Course guide: ' . pdf_ascii($shortname),
        'Course: ' . pdf_ascii($coursefullname),
        '',
        'Use this guide alongside the Moodle lessons and course forum.',
        'Course sections:',
    ];

    foreach ($sectiontitles as $index => $sectiontitle) {
        $clean = pdf_ascii($sectiontitle);
        $lines[] = ($index + 1) . '. ' . ($clean !== '' ? $clean : 'Lesson ' . ($index + 1));
    }

    $lines[] = '';
    $lines[] = 'Suggested study routine:';
    $lines[] = '1. Read the lesson page and watch the featured video when available.';
    $lines[] = '2. Complete a small practice task and record your questions.';
    $lines[] = '3. Share one useful takeaway in the course forum.';

    // Keep the PDF dependency-free: Moodle can generate this basic handout
    // without installing a second PDF library in the Docker image.
    $stream = "BT\n/F1 18 Tf\n50 760 Td\n";
    foreach ($lines as $index => $line) {
        $size = $index === 0 ? 18 : 11;
        if ($index !== 0) {
            $stream .= "0 -18 Td\n";
        }
        $stream .= '/' . 'F1 ' . $size . " Tf\n(" . pdf_escape($line) . ") Tj\n";
    }
    $stream .= "ET\n";

    $objects = [];
    $objects[] = '<< /Type /Catalog /Pages 2 0 R >>';
    $objects[] = '<< /Type /Pages /Kids [3 0 R] /Count 1 >>';
    $objects[] = '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] '
        . '/Resources << /Font << /F1 4 0 R >> >> /Contents 5 0 R >>';
    $objects[] = '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>';
    $objects[] = '<< /Length ' . strlen($stream) . " >>\nstream\n" . $stream . 'endstream';

    $pdf = "%PDF-1.4\n%\xE2\xE3\xCF\xD3\n";
    $offsets = [0];
    foreach ($objects as $number => $object) {
        $offsets[$number + 1] = strlen($pdf);
        $pdf .= ($number + 1) . " 0 obj\n" . $object . "\nendobj\n";
    }

    $xref = strlen($pdf);
    $pdf .= "xref\n0 " . (count($objects) + 1) . "\n";
    $pdf .= "0000000000 65535 f \n";
    for ($number = 1; $number <= count($objects); $number++) {
        $pdf .= sprintf('%010d 00000 n \n', $offsets[$number]);
    }
    $pdf .= "trailer\n<< /Size " . (count($objects) + 1) . " /Root 1 0 R >>\n"
        . "startxref\n" . $xref . "\n%%EOF\n";

    return $pdf;
}

function upsert_demo_page(int $courseid, int $sectionnumber, string $idnumber, string $name, string $content): array {
    global $DB;

    $existingcm = $DB->get_record('course_modules', [
        'course' => $courseid,
        'idnumber' => $idnumber,
    ], '*', IGNORE_MISSING);

    if ($existingcm) {
        $page = $DB->get_record('page', ['id' => $existingcm->instance], '*', IGNORE_MISSING);
        if ($page) {
            $page->name = $name;
            $page->content = $content;
            $page->contentformat = FORMAT_HTML;
            $page->timemodified = time();
            $DB->update_record('page', $page);
            set_coursemodule_name($existingcm->id, $name);
            return ['created' => 0, 'updated' => 1];
        }
    }

    $moduleinfo = (object)[
        'modulename' => 'page',
        'course' => $courseid,
        'section' => $sectionnumber,
        'visible' => 1,
        'visibleoncoursepage' => 1,
        'name' => $name,
        'introeditor' => ['text' => '', 'format' => FORMAT_HTML, 'itemid' => 0],
        'content' => $content,
        'contentformat' => FORMAT_HTML,
        'display' => RESOURCELIB_DISPLAY_OPEN,
        'printintro' => 0,
        'printlastmodified' => 1,
        'popupwidth' => 0,
        'popupheight' => 0,
        'cmidnumber' => $idnumber,
        'groupmode' => 0,
        'groupingid' => 0,
        'completion' => COMPLETION_TRACKING_NONE,
        'completionview' => 0,
        'completionexpected' => 0,
        'completiongradeitemnumber' => '',
        'tags' => [],
    ];
    create_module($moduleinfo);
    return ['created' => 1, 'updated' => 0];
}

function upsert_demo_pdf(int $courseid, int $sectionnumber, string $idnumber, string $name, string $intro, string $pdf): array {
    global $DB;

    $existingcm = $DB->get_record('course_modules', [
        'course' => $courseid,
        'idnumber' => $idnumber,
    ], '*', IGNORE_MISSING);

    if ($existingcm) {
        $resource = $DB->get_record('resource', ['id' => $existingcm->instance], '*', IGNORE_MISSING);
        $cmid = $existingcm->id;
        if ($resource) {
            $resource->name = $name;
            $resource->intro = $intro;
            $resource->introformat = FORMAT_HTML;
            $resource->timemodified = time();
            $DB->update_record('resource', $resource);
            set_coursemodule_name($cmid, $name);
        }
        $created = 0;
        $updated = 1;
    } else {
        $moduleinfo = (object)[
            'modulename' => 'resource',
            'course' => $courseid,
            'section' => $sectionnumber,
            'visible' => 1,
            'visibleoncoursepage' => 1,
            'name' => $name,
            'introeditor' => ['text' => $intro, 'format' => FORMAT_HTML, 'itemid' => 0],
            'display' => RESOURCELIB_DISPLAY_EMBED,
            'printintro' => 0,
            'popupwidth' => 0,
            'popupheight' => 0,
            'showdescription' => 0,
            'filterfiles' => 0,
            'cmidnumber' => $idnumber,
            'groupmode' => 0,
            'groupingid' => 0,
            'completion' => COMPLETION_TRACKING_NONE,
            'completionview' => 0,
            'completionexpected' => 0,
            'completiongradeitemnumber' => '',
            'tags' => [],
        ];
        $createdcm = create_module($moduleinfo);
        $cmid = $createdcm->coursemodule;
        $created = 1;
        $updated = 0;
    }

    $fs = get_file_storage();
    $context = context_module::instance($cmid);
    foreach ($fs->get_area_files($context->id, 'mod_resource', 'content', 0, 'id', false) as $file) {
        $file->delete();
    }
    $fs->create_file_from_string([
        'component' => 'mod_resource',
        'filearea' => 'content',
        'contextid' => $context->id,
        'itemid' => 0,
        'filepath' => '/',
        'filename' => 'course-guide.pdf',
    ], $pdf);

    return ['created' => $created, 'updated' => $updated];
}

function demo_question_id(int $categoryid, string $idnumber): ?int {
    global $DB;

    $sql = 'SELECT qv.questionid
              FROM {question_bank_entries} qbe
              JOIN {question_versions} qv ON qv.questionbankentryid = qbe.id
             WHERE qbe.questioncategoryid = ?
               AND qbe.idnumber = ?
          ORDER BY qv.version DESC';
    $questionid = $DB->get_field_sql($sql, [$categoryid, $idnumber], IGNORE_MISSING);
    return $questionid ? (int)$questionid : null;
}

function create_demo_question(int $courseid, int $sectionnumber, string $sectiontitle, string $shortname, array $lessondata = []): int {
    global $CFG, $DB, $USER;

    $context = context_course::instance($courseid);
    $category = question_get_top_category($context->id, true);
    $idnumber = 'demo-question-' . strtolower($shortname) . '-' . $sectionnumber;
    $existingid = demo_question_id($category->id, $idnumber);
    $arabic = strpos($shortname, 'ISLAMIC-') === 0;
    $questiontext = (string)($lessondata['question'] ?? (
        $arabic
            ? 'بعد دراسة «' . $sectiontitle . '»، ينبغي للمتعلم أن يشرح فكرة أساسية من هذا القسم.'
            : 'After studying "' . $sectiontitle . '", the learner should be able to explain one key idea from this section.'
    ));
    $generalfeedback = $arabic
        ? 'راجع صفحة الدرس وحاول الإجابة مرة أخرى عند الحاجة.'
        : 'Review the lesson page and try the question again if needed.';
    $correctfeedback = $arabic
        ? 'إجابة صحيحة. يمكنك الانتقال إلى القسم التالي.'
        : 'Correct. Continue to the next section.';
    $incorrectfeedback = $arabic
        ? 'راجع محتوى القسم ثم حاول مرة أخرى.'
        : 'Review the section content, then try again.';

    $fromform = (object)[
        'category' => (string)$category->id,
        'name' => ($arabic ? 'اختبار سريع: ' : 'Quick check: ') . $sectiontitle,
        'idnumber' => $idnumber,
        'questiontext' => [
            'text' => htmlspecialchars($questiontext, ENT_QUOTES, 'UTF-8'),
            'format' => FORMAT_HTML,
        ],
        'generalfeedback' => [
            'text' => $generalfeedback,
            'format' => FORMAT_HTML,
        ],
        'defaultmark' => 1,
        'penalty' => 0,
        'correctanswer' => !empty($lessondata['answer']) ? 1 : 0,
        'feedbacktrue' => [
            'text' => $correctfeedback,
            'format' => FORMAT_HTML,
        ],
        'feedbackfalse' => [
            'text' => $incorrectfeedback,
            'format' => FORMAT_HTML,
        ],
        'status' => 0,
    ];

    $qtype = question_bank::get_qtype('truefalse');
    if ($existingid) {
        $existing = question_bank::load_question_data($existingid);
        $existing->modifiedby = $USER->id;
        $qtype->save_question($existing, $fromform);
        question_bank::notify_question_edited($existingid);
        return $existingid;
    }

    $question = (object)[
        'id' => 0,
        'qtype' => 'truefalse',
        'createdby' => $USER->id,
        'modifiedby' => $USER->id,
        'category' => $category->id,
        'contextid' => $context->id,
        'parent' => 0,
        'idnumber' => null,
        'status' => 0,
    ];

    $saved = $qtype->save_question($question, $fromform);
    return (int)$saved->id;
}

function upsert_demo_quiz(int $courseid, int $sectionnumber, string $sectiontitle, string $shortname, array $lessondata = []): array {
    global $CFG, $DB;

    $idnumber = 'demo-quiz-' . strtolower($shortname) . '-' . $sectionnumber;
    $arabic = strpos($shortname, 'ISLAMIC-') === 0;
    $quizname = ($arabic ? 'اختبار سريع: ' : 'Quick check: ') . $sectiontitle;
    $quizintro = $arabic
        ? '<p>أجب عن هذا الاختبار القصير لإكمال القسم وفتح القسم التالي.</p>'
        : '<p>Complete this quick check to unlock the next section.</p>';
    $existingcm = $DB->get_record('course_modules', [
        'course' => $courseid,
        'idnumber' => $idnumber,
    ], '*', IGNORE_MISSING);

    if ($existingcm) {
        $cmid = (int)$existingcm->id;
        $quiz = $DB->get_record('quiz', ['id' => $existingcm->instance], '*', MUST_EXIST);
        $created = 0;
        $updated = 1;
    } else {
        $moduleinfo = (object)[
            'modulename' => 'quiz',
            'course' => $courseid,
            'section' => $sectionnumber,
            'visible' => 1,
            'visibleoncoursepage' => 1,
            'name' => $quizname,
            'introeditor' => [
                'text' => $quizintro,
                'format' => FORMAT_HTML,
                'itemid' => 0,
            ],
            'timeopen' => 0,
            'timeclose' => 0,
            'preferredbehaviour' => 'deferredfeedback',
            'canredoquestions' => 0,
            'attempts' => 0,
            'attemptonlast' => 0,
            'grademethod' => QUIZ_GRADEHIGHEST,
            'decimalpoints' => 2,
            'questiondecimalpoints' => -1,
            'questionsperpage' => 1,
            'shuffleanswers' => 1,
            'sumgrades' => 1,
            'grade' => 1,
            'timelimit' => 0,
            'overduehandling' => 'autosubmit',
            'graceperiod' => 86400,
            'quizpassword' => '',
            'subnet' => '',
            'browsersecurity' => '',
            'delay1' => 0,
            'delay2' => 0,
            'showuserpicture' => 0,
            'showblocks' => 0,
            'navmethod' => QUIZ_NAVMETHOD_FREE,
            'completion' => COMPLETION_TRACKING_AUTOMATIC,
            'completionview' => 0,
            'completionusegrade' => 0,
            'completionpassgrade' => 0,
            'completionattemptsexhausted' => 0,
            'completionminattemptsenabled' => 1,
            'completionminattempts' => 1,
            'completionunlocked' => 1,
            'cmidnumber' => $idnumber,
            'groupmode' => 0,
            'groupingid' => 0,
            'completionexpected' => 0,
            'completiongradeitemnumber' => '',
            'tags' => [],
        ];
        $createdcm = create_module($moduleinfo);
        $cmid = (int)$createdcm->coursemodule;
        $quiz = $DB->get_record('quiz', ['id' => $createdcm->instance], '*', MUST_EXIST);
        $created = 1;
        $updated = 0;
    }

    // Keep the completion rule explicit even if this activity already existed.
    $quiz->name = $quizname;
    $quiz->intro = $quizintro;
    $quiz->introformat = FORMAT_HTML;
    $quiz->completionminattempts = 1;
    $quiz->completionattemptsexhausted = 0;
    $DB->update_record('quiz', $quiz);
    $DB->set_field('course_modules', 'completion', COMPLETION_TRACKING_AUTOMATIC, ['id' => $cmid]);
    $DB->set_field('course_modules', 'completionpassgrade', 0, ['id' => $cmid]);

    $questionid = create_demo_question($courseid, $sectionnumber, $sectiontitle, $shortname, $lessondata);
    if (!$DB->record_exists('quiz_slots', ['quizid' => $quiz->id])) {
        $quiz->cmid = $cmid;
        quiz_add_quiz_question($questionid, $quiz, 1, 1);
    }

    return ['cmid' => $cmid, 'created' => $created, 'updated' => $updated];
}

function lock_demo_sections(int $courseid, array $sectionsbynumber, array $quizcms): int {
    global $DB;

    $locked = 0;
    foreach ($quizcms as $sectionnumber => $quizcmid) {
        $nextsectionnumber = $sectionnumber + 1;
        if (!isset($sectionsbynumber[$nextsectionnumber])) {
            continue;
        }

        $availability = json_encode(
            \core_availability\tree::get_root_json([
                \availability_completion\condition::get_json($quizcmid, COMPLETION_COMPLETE),
            ], '&')
        );
        $section = $sectionsbynumber[$nextsectionnumber];
        if ((string)$section->availability !== $availability) {
            $DB->set_field('course_sections', 'availability', $availability, ['id' => $section->id]);
            $locked++;
        }
    }
    return $locked;
}

$createdpages = 0;
$updatedpages = 0;
$createdsections = 0;
$renamedsections = 0;
$createdvideos = 0;
$updatedvideos = 0;
$createdpdfs = 0;
$updatedpdfs = 0;
$createdquizzes = 0;
$updatedquizzes = 0;
$lockedsections = 0;

foreach ($courses as $shortname => $sectiontitles) {
    $course = $DB->get_record('course', ['shortname' => $shortname], '*', IGNORE_MISSING);
    if (!$course) {
        foreach ($coursealiases[$shortname] ?? [] as $alias) {
            $course = $DB->get_record('course', ['shortname' => $alias], '*', IGNORE_MISSING);
            if ($course) {
                echo "Using legacy course {$alias} for {$shortname}\n";
                break;
            }
        }
    }
    if (!$course) {
        echo "Skipped missing course: {$shortname}\n";
        continue;
    }

    $lessons = $curriculum[$shortname] ?? [];

    $sections = $DB->get_records('course_sections', ['course' => $course->id], 'section');
    $sectionsbynumber = [];
    foreach ($sections as $sectionrecord) {
        $sectionsbynumber[(int)$sectionrecord->section] = $sectionrecord;
    }

    // Production databases may contain the course shell without the topic
    // sections that were present in the local demo database. Create the
    // expected section numbers before adding the curated activities.
    $missingsectionnumbers = [];
    foreach (array_keys($sectiontitles) as $index) {
        $sectionnumber = $index + 1;
        if (!isset($sectionsbynumber[$sectionnumber])) {
            $missingsectionnumbers[] = $sectionnumber;
        }
    }
    if ($missingsectionnumbers) {
        course_create_sections_if_missing($course->id, $missingsectionnumbers);
        $createdsections += count($missingsectionnumbers);
        $sections = $DB->get_records('course_sections', ['course' => $course->id], 'section');
        $sectionsbynumber = [];
        foreach ($sections as $sectionrecord) {
            $sectionsbynumber[(int)$sectionrecord->section] = $sectionrecord;
        }
    }

    // The general section is reserved for announcements/discussions.
    if (isset($sectionsbynumber[0]) && trim((string)$sectionsbynumber[0]->name) !== '') {
        $DB->set_field('course_sections', 'name', null, ['id' => $sectionsbynumber[0]->id]);
        $renamedsections++;
    }

    foreach ($sectiontitles as $index => $sectiontitle) {
        $sectionnumber = $index + 1;
        if (!isset($sectionsbynumber[$sectionnumber])) {
            echo "Skipped missing section {$shortname}:{$sectionnumber}\n";
            continue;
        }

        $section = $sectionsbynumber[$sectionnumber];
        if ((string)$section->name !== $sectiontitle) {
            $DB->set_field('course_sections', 'name', $sectiontitle, ['id' => $section->id]);
            $renamedsections++;
        }

        $idnumber = 'demo-content-' . strtolower($shortname) . '-' . $sectionnumber;
        $lessondata = $lessons[$index] ?? [];
        $content = demo_content($shortname, $course->fullname, $sectiontitle, $lessondata);
        $result = upsert_demo_page($course->id, $sectionnumber, $idnumber, $sectiontitle, $content);
        $createdpages += $result['created'];
        $updatedpages += $result['updated'];
    }

    if (isset($videos[$shortname]) && isset($sectionsbynumber[1])) {
        $video = $videos[$shortname];
        $videoname = 'Featured video: ' . $video['title'];
        $result = upsert_demo_page(
            $course->id,
            1,
            'demo-video-' . strtolower($shortname),
            $videoname,
            demo_video_content($shortname, $video)
        );
        $createdvideos += $result['created'];
        $updatedvideos += $result['updated'];
    }

    $pdfname = 'Course guide PDF';
    $pdfintro = '<p>Download this course guide for a printable overview of the lessons and study routine.</p>';
    $result = upsert_demo_pdf(
        $course->id,
        1,
        'demo-pdf-' . strtolower($shortname),
        $pdfname,
        $pdfintro,
        demo_pdf($shortname, $course->fullname, $sectiontitles)
    );
    $createdpdfs += $result['created'];
    $updatedpdfs += $result['updated'];

    $quizcms = [];
    foreach ($sectiontitles as $index => $sectiontitle) {
        $sectionnumber = $index + 1;
        if (!isset($sectionsbynumber[$sectionnumber])) {
            continue;
        }
        $lessondata = $lessons[$index] ?? [];
        $result = upsert_demo_quiz($course->id, $sectionnumber, $sectiontitle, $shortname, $lessondata);
        $quizcms[$sectionnumber] = $result['cmid'];
        $createdquizzes += $result['created'];
        $updatedquizzes += $result['updated'];
    }
    $lockedsections += lock_demo_sections($course->id, $sectionsbynumber, $quizcms);

    rebuild_course_cache($course->id, true);
    echo "Seeded {$shortname}: " . count($sectiontitles) . " sections\n";
}

purge_all_caches();
echo "Created sections: {$createdsections}; renamed sections: {$renamedsections}; "
    . "created pages: {$createdpages}; updated pages: {$updatedpages}; "
    . "created videos: {$createdvideos}; updated videos: {$updatedvideos}; "
    . "created PDFs: {$createdpdfs}; updated PDFs: {$updatedpdfs}; "
    . "created quizzes: {$createdquizzes}; updated quizzes: {$updatedquizzes}; "
    . "locked sections: {$lockedsections}\n";
