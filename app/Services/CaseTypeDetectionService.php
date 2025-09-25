<?php

namespace App\Services;

class CaseTypeDetectionService
{
    protected $keywordMap = [
        'murder' => 'Murder',
        'homicide' => 'Murder',
        'killed' => 'Murder',

        'theft' => 'Theft',
        'stolen' => 'Theft',
        'robbery' => 'Theft',

        'assault' => 'Assault',
        'attacked' => 'Assault',
        'beaten' => 'Assault',

        'fraud' => 'Fraud',
        'scam' => 'Fraud',
        'embezzlement' => 'Fraud',

        'accident' => 'Traffic Offense',
        'drunk driving' => 'Traffic Offense',
        'hit and run' => 'Traffic Offense',
        'hit-and-run' => 'Traffic Offense',

        'noise' => 'Nuisance',
        'disturbance' => 'Nuisance',
    ];

    protected $caseDepartmentPriorityMap = [
        'Murder' => ['department_id' => 1, 'priority' => 'High'],
        'Theft' => ['department_id' => 1, 'priority' => 'High'],
        'Assault' => ['department_id' => 1, 'priority' => 'High'],
        'Fraud' => ['department_id' => 2, 'priority' => 'Moderate'],
        'Traffic Offense' => ['department_id' => 3, 'priority' => 'High'],
        'Nuisance' => ['department_id' => 2, 'priority' => 'Low'],
    ];

    public function detectCaseType(string $statement): ?string
    {
        $statement = strtolower($statement);

        foreach ($this->keywordMap as $keyword => $type) {
            if (str_contains($statement, $keyword)) {
                return $type;
            }
        }

        return null; // No valid police-handled case found
    }

    public function getCaseDetails(string $caseType): ?array
    {
        return $this->caseDepartmentPriorityMap[$caseType] ?? null;
    }
}
