<?php
/*
| Organization backend - Project form validation (shared)
| Used by: Backend/Organization/post.php and Backend/Organization/edit_project.php
|
| Rules:
|   Save Draft -> only the Title is required (the rest can be finished later),
|                 but anything that IS filled must still be correct.
|   Publish    -> every field marked * on the form is required:
|                 Title, Category, Required Skills, Description,
|                 Duration, Students Required, Application Deadline.
|   Learning Objectives, Expected Outcomes and attachments are optional.
|
| The same rules are checked in the browser by Assets/JS/Organization/project-validation.js,
| but the server always checks again (the browser check can be skipped).
*/

// (the categories come from the project_categories table, see orgCategories() in org_db.php)
const PROJECT_DIFFICULTIES = ['Beginner', 'Intermediate', 'Advanced'];
const PROJECT_YEARS        = ['Any Year', 'Year 1', 'Year 2', 'Year 3', 'Year 4'];
const PROJECT_VISIBILITY   = ['Public', 'Private'];

const TITLE_MIN = 5;      const TITLE_MAX = 100;
const DESC_MIN  = 50;     const DESC_MAX  = 3000;
const TEXT_MAX  = 1500;   // Learning Objectives / Expected Outcomes
const SKILLS_MAX = 10;    const SKILL_MAX_LEN = 30;
const WEEKS_MAX = 52;     // 1 year
const STUDENTS_MAX = 10;  // team size

/**
 * "React, , python ,React" -> ['React', 'python']  (trimmed, no empty, no duplicates)
 */
function cleanSkills(string $keywords): array
{
    $skills = [];
    foreach (explode(',', $keywords) as $skill) {
        $skill = trim($skill);
        if ($skill === '') continue;
        // same skill typed twice (any case) is kept once
        if (!in_array(strtolower($skill), array_map('strtolower', $skills), true)) {
            $skills[] = $skill;
        }
    }
    return $skills;
}

/**
 * Checks the project form.
 * $d       = the posted values (title, category, keywords, description, ...)
 * $isDraft = true for "Save Draft" (only the title is required)
 * $extra   = 'categories'   => the category names from project_categories (orgCategories())
 *            only for Edit Project / Hold:
 *            'old_deadline' => deadline saved before (Y-m-d); if it is not changed it is not checked again
 *            'min_students' => students already in the team
 * Returns  ['field name' => 'error message'] (empty array = no errors)
 */
function validateProjectForm(array $d, bool $isDraft, array $extra = []): array
{
    $errors = [];

    // ---- Title (always required, also for a draft) ----
    $len = mb_strlen($d['title']);
    if ($len === 0) {
        $errors['title'] = 'Project title is required.';
    } elseif ($len < TITLE_MIN || $len > TITLE_MAX) {
        $errors['title'] = 'Title must be ' . TITLE_MIN . ' to ' . TITLE_MAX . ' characters.';
    }

    // ---- Category ----
    $allowedCategories = $extra['categories'] ?? [];

    if ($d['category'] === '') {
        if (!$isDraft) $errors['category'] = 'Please select a category.';
    } elseif (!in_array($d['category'], $allowedCategories, true)) {
        $errors['category'] = 'Please select a category from the list.';
    }

    // ---- Required Skills ----
    $skills = cleanSkills($d['keywords']);
    if (count($skills) === 0) {
        if (!$isDraft) $errors['keywords'] = 'Add at least one required skill.';
    } elseif (count($skills) > SKILLS_MAX) {
        $errors['keywords'] = 'You can add up to ' . SKILLS_MAX . ' skills.';
    } else {
        foreach ($skills as $skill) {
            if (mb_strlen($skill) > SKILL_MAX_LEN) {
                $errors['keywords'] = 'Each skill can have up to ' . SKILL_MAX_LEN . ' characters.';
                break;
            }
        }
        // keep the whole list short (it is shown on the project cards)
        if (!isset($errors['keywords']) && mb_strlen(implode(', ', $skills)) > 255) {
            $errors['keywords'] = 'The skills list is too long. Remove a few skills.';
        }
    }

    // ---- Description ----
    $len = mb_strlen($d['description']);
    if ($len === 0) {
        if (!$isDraft) $errors['description'] = 'Project description is required.';
    } elseif ($len < DESC_MIN) {
        $errors['description'] = 'Description must have at least ' . DESC_MIN . ' characters (now ' . $len . ').';
    } elseif ($len > DESC_MAX) {
        $errors['description'] = 'Description can have up to ' . DESC_MAX . ' characters.';
    }

    // ---- Learning Objectives / Expected Outcomes (optional) ----
    if (mb_strlen($d['learning_objectives']) > TEXT_MAX) {
        $errors['learning_objectives'] = 'Learning objectives can have up to ' . TEXT_MAX . ' characters.';
    }
    if (mb_strlen($d['expected_outcomes']) > TEXT_MAX) {
        $errors['expected_outcomes'] = 'Expected outcomes can have up to ' . TEXT_MAX . ' characters.';
    }

    // ---- Difficulty / Preferred Year / Visibility (always have a value on the form) ----
    if (!in_array($d['difficulty'], PROJECT_DIFFICULTIES, true)) {
        $errors['difficulty'] = 'Please choose a difficulty level.';
    }
    if (!in_array($d['preferred_year'], PROJECT_YEARS, true)) {
        $errors['preferred_year'] = 'Please choose an academic year from the list.';
    }
    if (!in_array($d['visibility'], PROJECT_VISIBILITY, true)) {
        $errors['visibility'] = 'Please choose Public or Private.';
    }

    // ---- Duration (weeks): whole number 1 - 52 ----
    if ($d['duration_weeks'] === '') {
        if (!$isDraft) $errors['duration_weeks'] = 'Project duration is required.';
    } elseif (!ctype_digit($d['duration_weeks']) || (int)$d['duration_weeks'] < 1 || (int)$d['duration_weeks'] > WEEKS_MAX) {
        $errors['duration_weeks'] = 'Enter a whole number of weeks from 1 to ' . WEEKS_MAX . '.';
    }

    // ---- Students Required: whole number 1 - 10 ----
    $minStudents = max(1, (int)($extra['min_students'] ?? 1));
    if ($d['students_required'] === '') {
        if (!$isDraft) $errors['students_required'] = 'Number of students is required.';
    } elseif (!ctype_digit($d['students_required']) || (int)$d['students_required'] < 1 || (int)$d['students_required'] > STUDENTS_MAX) {
        $errors['students_required'] = 'Enter a whole number from 1 to ' . STUDENTS_MAX . '.';
    } elseif ((int)$d['students_required'] < $minStudents) {
        // Edit Project: can't ask for fewer students than are already in the team
        $errors['students_required'] = $minStudents . ' students are already in the team, so it can\'t be less than ' . $minStudents . '.';
    }

    // ---- Application Deadline: a real date, after today, within 1 year ----
    if ($d['deadline'] === '') {
        if (!$isDraft) $errors['deadline'] = 'Application deadline is required.';
    } else {
        $date = DateTime::createFromFormat('Y-m-d', $d['deadline']);
        if (!$date || $date->format('Y-m-d') !== $d['deadline']) {
            $errors['deadline'] = 'Please choose a valid date.';
        } elseif ($d['deadline'] !== ($extra['old_deadline'] ?? null)) {
            // a new / changed deadline must give students time to apply
            if ($d['deadline'] <= date('Y-m-d')) {
                $errors['deadline'] = 'The deadline must be after today.';
            } elseif ($d['deadline'] > date('Y-m-d', strtotime('+1 year'))) {
                $errors['deadline'] = 'The deadline can be at most 1 year from today.';
            }
        }
    }

    return $errors;
}

/**
 * Error box under a field. It is always printed (empty when there is no error)
 * so the browser check (project-validation.js) can write into the same place.
 */
function fieldError(array $errors, string $field): string
{
    return '<div class="field-error" data-for="' . $field . '">'
         . htmlspecialchars($errors[$field] ?? '') . '</div>';
}