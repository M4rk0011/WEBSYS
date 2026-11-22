<?php
/**
 * Handles requests and validation for the Residents module.
 * Maintainer note: keep application logic unchanged when editing documentation.
 */
$page = 'residents';
$pageTitle = 'Inhabitants profiling';
require_once __DIR__ . '/../middleware/init.php';
demand(canManageDirectory());
$store = directory('residents');
if ($handleForm) {
    try {
        checkFormToken();
        demand(in_array($action, ['save-resident', 'delete-resident'], true));
        $id = post('id');
        if ($id !== '' && !preg_match('/\A[a-f0-9]{24}\z/D', $id)) throw new RuntimeException('Invalid resident ID.');
        if ($action === 'delete-resident') {
            demand($id !== '' && $store->find($id) !== null);
            $store->delete($id);
            $_SESSION['flash'] = 'Resident record deleted.';
        } else {
            $birth = inputText('birth_date', 10);
            if (!validDateOrBlank($birth) || ($birth !== '' && $birth > date('Y-m-d'))) {
                throw new RuntimeException('Choose a valid date of birth.');
            }
            $sex = inputText('sex', 20);
            demand(in_array($sex, ['', 'female', 'male', 'not_specified'], true));
            $store->save([
                'id' => $id,
                'first_name' => inputText('first_name', 100, true),
                'middle_name' => inputText('middle_name', 100),
                'last_name' => inputText('last_name', 100, true),
                'birth_date' => $birth,
                'sex' => $sex,
                'phone' => inputText('phone', 40),
                'address' => inputText('address', 250, true),
                'household_no' => inputText('household_no', 80),
                'notes' => inputText('notes', 1500),
            ]);
            $_SESSION['flash'] = 'Resident record saved.';
        }
        go('residents');
    } catch (PDOException $e) {
        $error = 'Could not save the resident. Check your database setup.';
    } catch (RuntimeException $e) {
        $error = $e->getMessage();
    }
}
$edit = null;
if (is_string($_GET['edit'] ?? null) && $_GET['edit'] !== '') {
    $edit = $store->find($_GET['edit']);
    if (!$edit) { http_response_code(404); exit('Resident record not found.'); }
}
$search = trim(is_string($_GET['q'] ?? null) ? $_GET['q'] : '');
$records = $store->all();
if ($search !== '') $records = array_values(array_filter($records, fn($r) => stripos(
    $r['first_name'] . ' ' . $r['middle_name'] . ' ' . $r['last_name'] . ' ' . $r['address'] . ' ' . $r['household_no'], substr($search, 0, 100)
) !== false));
