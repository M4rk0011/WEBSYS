<?php
// This file answers: who is allowed to open a page or change a complaint?
// Hiding a button is not enough. The form handlers also call these functions.

// Keep old DATABASE2 accounts working with their new role names.
function role(?object $user = null): string
{
    $user = $user ?? ($GLOBALS['currentUser'] ?? null);
    $name = $user->role ?? '';
    $oldRoles = ['admin' => 'system_admin', 'security' => 'personnel', 'user' => 'resident'];
    return $oldRoles[$name] ?? $name;
}

// Both administrator roles manage the barangay system.
function isAdmin(): bool
{
    return in_array(role(), ['system_admin', 'lgu_admin'], true);
}

// Only residents may write complaints. Staff process complaints instead.
function canSubmitComplaint(): bool
{
    $user = $GLOBALS['currentUser'] ?? null;
    return $user && $user->isActive && role() === 'resident' && ($user->verified ?? false);
}

// Personnel can process assigned work; only managers see summary reports.
function canUpdateStatus(): bool
{
    return in_array(role(), ['system_admin', 'lgu_admin', 'department_head', 'personnel'], true);
}
function canViewAnalytics(): bool
{
    return in_array(role(), ['system_admin', 'lgu_admin', 'department_head'], true);
}

// The same page list controls the menu AND direct URL access.
function allowedPages(): array
{
    $pages = ['dashboard', 'complaints', 'details', 'map', 'announcements', 'profile'];
    if (role() === 'resident') {
        $pages[] = 'report';
    }
    if (canViewAnalytics()) {
        $pages[] = 'monitoring';
        $pages[] = 'analytics';
    }
    if (isAdmin()) {
        $pages[] = 'users';
        $pages[] = 'departments';
        $pages[] = 'residents';
        $pages[] = 'businesses';
        $pages[] = 'map-places';
    }
    if (!in_array(role(), ['system_admin', 'lgu_admin', 'department_head', 'personnel', 'resident'], true)) {
        return [];
    }
    return $pages;
}

// Check whether the resident originally submitted this complaint.
function owns(object $complaint): bool
{
    $user = $GLOBALS['currentUser'] ?? null;
    return $user && in_array($complaint->reportedBy, [$user->id, $user->username], true);
}

// This filter also protects maps, evidence photos and PDF downloads.
function canView(object $complaint): bool
{
    $user = $GLOBALS['currentUser'] ?? null;
    if (!$user || !$user->isActive) {
        return false;
    }
    if (isAdmin()) {
        return true;
    }
    if (role() === 'resident') {
        return owns($complaint);
    }
    if (role() === 'department_head') {
        return !empty($user->departmentId) && $user->departmentId === $complaint->departmentId;
    }
    if (role() === 'personnel') {
        // A moved staff member cannot keep access to work in the previous department.
        return !empty($user->departmentId)
            && $user->departmentId === $complaint->departmentId
            && in_array($complaint->assignedTo, [$user->id, $user->username], true);
    }
    return false;
}

// Staff can change processing status only within their own access scope.
function canProcess(object $complaint): bool
{
    return canUpdateStatus() && canView($complaint);
}

// Heads assign within their department; administrators assign across departments.
function canAssign(object $complaint): bool
{
    return isAdmin() || (role() === 'department_head' && canView($complaint));
}

// Staff cannot rewrite the resident's complaint, contact details or evidence.
function canEditConcernContent(?object $complaint): bool
{
    return $complaint && canSubmitComplaint() && owns($complaint) && $complaint->status === 'pending';
}

function isAdminUser(object $user): bool
{
    return in_array(role($user), ['system_admin', 'lgu_admin'], true);
}

// Stop a submitted form when the account lacks permission.
function demand(bool $allowed): void
{
    if (!$allowed) {
        http_response_code(403);
        throw new RuntimeException('You do not have permission to perform this action.');
    }
}
