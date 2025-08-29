<?php

namespace Database\Seeders;

use App\Models\InterviewQuestion;
use Illuminate\Database\Seeder;
use Schema;

class InterviewQuestionSeeder extends Seeder
{
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        InterviewQuestion::query()->truncate();
        Schema::enableForeignKeyConstraints();
        $items = [

            [
                'title' => 'Tell me about your biggest failure.',
                'category' => 'Behavioral',
                'difficulty' => 'Hard',
                'explanation' => <<<'TXT'
This question tests accountability, resilience, and learning agility—not the size of the failure. Show that you can self‑diagnose and improve.

How to answer:
- Pick a real, consequential failure you owned (not a group cop‑out).
- Give brief context → what went wrong → your contribution.
- Root‑cause analysis (what you misjudged / missed).
- Corrective actions you implemented and the measurable outcome later.
- What you do differently now in similar situations.
Common mistakes: blaming others, picking a trivial story, or wallowing without showing growth.
TXT,
            ],

            [
                'title' => 'Tell me about yourself.',
                'category' => 'General',
                'difficulty' => 'Easy',
                'explanation' => <<<'TXT'
This is a positioning prompt. They want your most relevant headline, not a life story.

How to answer (Present → Past → Future):
- Present: Your role/scope and key strengths relevant to the job.
- Past: 1–2 concrete achievements that map to the role’s needs.
- Future: What you’re looking to do next and why this role fits.
Keep it 60–90 seconds, quantified, and aligned to the job description.
TXT,
            ],
            [
                'title' => 'What are your strengths?',
                'category' => 'General',
                'difficulty' => 'Medium',
                'explanation' => <<<'TXT'
They’re checking for self‑awareness and real impact.

How to answer:
- Pick 2–3 strengths that directly solve their problems.
- Prove each with a quick quantified example (metric, speed, quality, cost).
- Tie each strength to how it will help in this role.
Avoid generic fluff like “hard‑working” without evidence.
TXT,
            ],
            [
                'title' => 'What are your weaknesses?',
                'category' => 'General',
                'difficulty' => 'Medium',
                'explanation' => <<<'TXT'
Honest self‑diagnosis + mitigation strategy is the goal.

How to answer:
- Choose a real, non‑fatal weakness (not core to the role’s must‑haves).
- Give a brief example of when it showed up.
- Describe concrete actions you’ve taken to improve and current results.
Avoid humble‑brags (“I care too much”) and anything that’s a deal‑breaker for the role.
TXT,
            ],
            [
                'title' => 'Why are you interested in our company?',
                'category' => 'General',
                'difficulty' => 'Medium',
                'explanation' => <<<'TXT'
They’re filtering for signal over spray‑and‑pray applications.

How to answer:
- Reference specific company facts: product, market, mission, tech, recent news.
- Connect those facts to your experience and career goals.
- Explain the value you can add to their stage (e.g., scaling, refactor, GTM).
Generic compliments or vague culture talk = auto‑reject.
TXT,
            ],
            [
                'title' => 'Why are you leaving your current job?',
                'category' => 'General',
                'difficulty' => 'Medium',
                'explanation' => <<<'TXT'
They want to detect risk factors (toxicity, performance issues) and fit.

How to answer:
- Stay positive and forward‑looking: growth, scope, tech stack, impact.
- Avoid dumping on your employer/manager.
- Tie the move to what this role uniquely offers.
Keep it factual, not emotional.
TXT,
            ],
            [
                'title' => 'Where do you see yourself five years from now?',
                'category' => 'General',
                'difficulty' => 'Medium',
                'explanation' => <<<'TXT'
They’re gauging ambition, realism, and alignment.

How to answer:
- Describe skills/impact you want to compound (e.g., leading X, owning Y).
- Show a path that’s plausible within their org (IC vs. leadership, domain depth).
- Emphasize learning velocity and delivering outcomes along the way.
Avoid rigid titles or fantasies that don’t exist in their structure.
TXT,
            ],
            [
                'title' => 'What are your salary expectations?',
                'category' => 'General',
                'difficulty' => 'Medium',
                'explanation' => <<<'TXT'
They’re testing your market awareness and flexibility.

How to answer:
- Offer a researched range based on market, level, and location.
- Anchor to total compensation (base + bonus/equity/benefits) and role scope.
- Signal flexibility: “Open to discussing a fair package aligned to the responsibilities.”
Avoid lowballing or cornering yourself too early.
TXT,
            ],
            [
                'title' => 'What do you know about our company?',
                'category' => 'General',
                'difficulty' => 'Medium',
                'explanation' => <<<'TXT'
They’re checking if you prepared for the interview.

How to answer:
- One‑liner on what the company does and who it serves.
- Key products, customers, tech, market position, recent milestones.
- Why those matter to you and how you can contribute.
Keep it specific; avoid reading their homepage back to them.
TXT,
            ],
            [
                'title' => 'What do you know about the position you are applying for?',
                'category' => 'General',
                'difficulty' => 'Medium',
                'explanation' => <<<'TXT'
They want to confirm role comprehension.

How to answer:
- Summarize core responsibilities and success criteria.
- Call out the key skills/tools/processes required.
- Map your experience to each area with quick examples.
Ask 1 targeted clarification to show depth, not ignorance.
TXT,
            ],
            [
                'title' => 'What skills do you have that make you suitable for this job?',
                'category' => 'Technical',
                'difficulty' => 'Medium',
                'explanation' => <<<'TXT'
Skill‑to‑JD mapping.

How to answer:
- Select 3–5 must‑have skills from the JD.
- For each: proof via a concise STAR/metric example.
- Mention adjacent skills that reduce onboarding time.
Prioritize relevance over breadth.
TXT,
            ],
            [
                'title' => 'What contributions can you make if hired?',
                'category' => 'Behavioral',
                'difficulty' => 'Hard',
                'explanation' => <<<'TXT'
They want immediate value, not vague potential.

How to answer:
- First 30/60/90‑day plan (learn → deliver → improve).
- Name 1–2 quick wins (e.g., fix X bug class, optimize Y pipeline, document Z).
- Longer‑term impact tied to KPIs (quality, speed, revenue, cost).
Ground it in their stack/process, not generic platitudes.
TXT,
            ],
            [
                'title' => 'Why should we hire you?',
                'category' => 'General',
                'difficulty' => 'Hard',
                'explanation' => <<<'TXT'
Closing argument = fit × evidence.

How to answer:
- 2–3 differentiators that map to top role requirements.
- Proof points with metrics.
- Contrast with typical candidates (politely) and tie to team goals.
Make it crisp; this is not a recap of your entire CV.
TXT,
            ],
            [
                'title' => 'What is your greatest achievement?',
                'category' => 'Behavioral',
                'difficulty' => 'Medium',
                'explanation' => <<<'TXT'
They’re looking for scale of impact and ownership.

How to answer:
- Pick something measurable and relevant (revenue, performance, users, reliability).
- Explain your role vs. team; call out constraints and trade‑offs.
- Share the enduring result (what still exists today).
Avoid school projects if you have work experience with better signal.
TXT,
            ],
            [
                'title' => 'Describe a time when you overcame a difficult challenge at work.',
                'category' => 'Behavioral',
                'difficulty' => 'Hard',
                'explanation' => <<<'TXT'
Classic STAR prompt targeting problem‑solving under constraints.

How to answer:
- Situation/Task in 1–2 lines.
- Actions you took (decision points, trade‑offs, collaboration).
- Result with metrics and lessons learned.
Focus on your decisions, not the team’s generic effort.
TXT,
            ],
            [
                'title' => 'How do you handle conflicts within a team?',
                'category' => 'Behavioral',
                'difficulty' => 'Hard',
                'explanation' => <<<'TXT'
They’re testing professionalism and communication under friction.

How to answer:
- Example of a real conflict (scope, design, ownership).
- Steps: clarify goals, surface assumptions, use data, propose options, decide.
- Outcome: resolution, relationship preserved, process improvement.
Avoid “I avoid conflict” — that’s not leadership.
TXT,
            ],
            [
                'title' => 'How do you handle stress and pressure?',
                'category' => 'Behavioral',
                'difficulty' => 'Medium',
                'explanation' => <<<'TXT'
They want to know you won’t melt down on deadlines/incidents.

How to answer:
- Personal system: prioritization, timeboxing, checklists, postmortems.
- Preventive habits: early risk flags, asking for help, automation.
- Example of a crunch period and how you protected quality.
Generic “I work harder” is not a system.
TXT,
            ],
            [
                'title' => 'Describe a time when you had to multitask and manage multiple projects; how did you stay organised and meet deadlines?',
                'category' => 'Behavioral',
                'difficulty' => 'Hard',
                'explanation' => <<<'TXT'
Signal: personal operating system.

How to answer:
- Tools/process: Kanban, sprint planning, calendar blocks, status updates.
- Prioritization framework (RICE/MoSCoW/impact vs. effort).
- Trade‑offs made and stakeholder communication.
- Outcome metrics (on‑time delivery, defect rate, stakeholder NPS).
TXT,
            ],
            [
                'title' => 'Describe your experience leading a team or project.',
                'category' => 'Behavioral',
                'difficulty' => 'Hard',
                'explanation' => <<<'TXT'
They’re probing leadership style and execution.

How to answer:
- Scope: team size, cross‑funcs, budget/timeline.
- Leadership actions: goal setting, delegation, risk mgmt, reviews, retros.
- Results: shipped what, measured how, impact where.
- One lesson that changed how you lead.
TXT,
            ],
            [
                'title' => 'Tell me about a time when you had to manage a dissatisfied customer or client.',
                'category' => 'Behavioral',
                'difficulty' => 'Hard',
                'explanation' => <<<'TXT'
Tests empathy + commercial sense.

How to answer:
- Context: what failed vs. expectations.
- Actions: listen, restate, propose options with trade‑offs.
- Quick win + longer‑term fix; close the loop.
- Outcome: churn prevented, upsell, CSAT — with numbers.
TXT,
            ],
            [
                'title' => 'How do you prioritise tasks when working?',
                'category' => 'Behavioral',
                'difficulty' => 'Medium',
                'explanation' => <<<'TXT'
Looking for structured decision‑making.

How to answer:
- Framework (impact/risk/urgency/effort) + who you align with.
- Example of conflicting priorities and how you resolved it.
- How you protect maker time and avoid thrash.
TXT,
            ],
            [
                'title' => 'Tell us about a time you made a mistake and how you handled it.',
                'category' => 'Behavioral',
                'difficulty' => 'Hard',
                'explanation' => <<<'TXT'
Ownership over optics.

How to answer:
- State the mistake plainly; no excuses.
- Immediate mitigation, communication, and prevention steps.
- What changed in your process to avoid repeats.
- Result (e.g., MTTR reduced, review checklist adopted).
TXT,
            ],
            [
                'title' => 'What kind of work environment do you prefer?',
                'category' => 'General',
                'difficulty' => 'Easy',
                'explanation' => <<<'TXT'
Cultural alignment check.

How to answer:
- Describe environments where you do your best work (feedback cadence, autonomy, documentation, async vs. synchronous).
- Connect to signals you’ve seen from their team.
Avoid trashing past workplaces.
TXT,
            ],
            [
                'title' => 'What motivates you or makes you enthusiastic about work?',
                'category' => 'General',
                'difficulty' => 'Medium',
                'explanation' => <<<'TXT'
They want durable motivation, not perks.

How to answer:
- Intrinsic drivers: hard problems, user impact, craftsmanship, learning.
- Example where motivation translated into measurable outcomes.
Tie it to what this role offers.
TXT,
            ],
            [
                'title' => 'Are you willing to work overtime?',
                'category' => 'General',
                'difficulty' => 'Easy',
                'explanation' => <<<'TXT'
This probes flexibility vs. boundaries.

How to answer:
- Reasonable stance: willing for true incidents/deadlines; prefer sustainable pace.
- Emphasize prevention (planning, scope control) over heroics.
TXT,
            ],
            [
                'title' => 'Are you willing to relocate or work outside the city?',
                'category' => 'General',
                'difficulty' => 'Easy',
                'explanation' => <<<'TXT'
Simple logistics question. Be honest about constraints and timelines. Offer alternatives (remote, periodic travel) if relocation isn’t viable.
TXT,
            ],
            [
                'title' => 'When can you start working?',
                'category' => 'General',
                'difficulty' => 'Easy',
                'explanation' => <<<'TXT'
State your notice period and any hard commitments. If flexible, give a range and conditions (e.g., buy‑out, vacation pre‑booked). Don’t over‑promise.
TXT,
            ],
            [
                'title' => 'What makes you unique?',
                'category' => 'General',
                'difficulty' => 'Hard',
                'explanation' => <<<'TXT'
Differentiate with intersectional strengths that matter to the role.

How to answer:
- Combine 2–3 capabilities (e.g., backend + product sense + FinTech domain).
- Back with one concrete story + metric.
Avoid buzzwords without proof.
TXT,
            ],
            [
                'title' => 'Do you have any questions for us?',
                'category' => 'General',
                'difficulty' => 'Easy',
                'explanation' => <<<'TXT'
Signal: curiosity and due diligence.

Good questions target:
- Success metrics for the role in 6–12 months.
- Team’s biggest bottlenecks and how this role helps.
- Engineering/product rituals and quality bars.
- Roadmap risks and how decisions are made.
Avoid questions whose answers are on the website.
TXT,
            ],
            [
                'title' => 'Why is there a gap in your employment history?',
                'category' => 'Behavioral',
                'difficulty' => 'Medium',
                'explanation' => <<<'TXT'
They’re assessing risk and continuity.

How to answer:
- Be factual and succinct (study, caregiving, travel, health, layoff, startup try).
- Highlight any upskilling or relevant projects during the gap.
- Reassure readiness and currency of skills.
Avoid defensiveness or oversharing.
TXT,
            ],
        ];

        foreach ($items as $item) {
            InterviewQuestion::create([
                'title' => $item['title'],
                'category' => $item['category'],
                'difficulty' => $item['difficulty'],
                'views_count' => random_int(40, 500),
                'users_practiced_count' => random_int(10, 30),
                'explanation' => $item['explanation'],
                'created_at' => now()->subDays(random_int(1, 30)),
                'updated_at' => now(),
            ]);
        }
    }
}
