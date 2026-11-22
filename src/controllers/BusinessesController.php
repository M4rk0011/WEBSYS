<?php
$page = 'businesses';
$pageTitle = 'Business establishment profiling';
require_once __DIR__ . '/../middleware/init.php';
demand(canManageDirectory());
$store = directory('businesses');
if ($handleForm) {
    try {
        checkFormToken();
        demand(in_array($action, ['save-business', 'delete-business'], true));
        $id = post('id');
        if ($id !== '' && !preg_match('/\A[a-f0-9]{24}\z/D', $id)) throw new RuntimeException('Invalid business ID.');
        if ($action === 'delete-business') {
            demand($id !== '' && $store->find($id) !== null);
            $store->delete($id);
            $_SESSION['flash'] = 'Business record deleted.';
        } else {
            $status = inputText('cctv_status', 15);
            demand(in_array($status, ['yes', 'no', 'unknown'], true));
            $count = filter_var(post('cctv_count'), FILTER_VALIDATE_INT);
            if ($count === false || $count < 0 || $count > 9999) {
                throw new RuntimeException('Enter a valid CCTV camera count.');
            }
            if ($status !== 'yes') $count = 0;
            $renewal = inputText('renewal_date', 10);
            if (!validDateOrBlank($renewal)) throw new RuntimeException('Choose a valid renewal date.');
            $store->save([
                'id' => $id,
                'business_name' => inputText('business_name', 180, true),
                'owner_name' => inputText('owner_name', 160),
                'business_type' => inputText('business_type', 120),
                'address' => inputText('address', 250, true),
                'contact' => inputText('contact', 100),
                'permit_no' => inputText('permit_no', 100),
                'renewal_date' => $renewal,
                'cctv_status' => $status,
                'cctv_count' => $count,
                'notes' => inputText('notes', 1500),
            ]);
            $_SESSION['flash'] = 'Business establishment saved.';
        }
        go('businesses');
    } catch (PDOException $e) {
        $error = 'Could not save the business. Check your database setup.';
    } catch (RuntimeException $e) {
        $error = $e->getMessage();
    }
}
$edit = null;
if (is_string($_GET['edit'] ?? null) && $_GET['edit'] !== '') {
    $edit = $store->find($_GET['edit']);
    if (!$edit) { http_response_code(404); exit('Business record not found.'); }
}
$search = trim(is_string($_GET['q'] ?? null) ? $_GET['q'] : '');
$filter = is_string($_GET['cctv'] ?? null) ? $_GET['cctv'] : '';
if (!in_array($filter, ['', 'yes', 'no', 'unknown'], true)) $filter = '';
$records = $store->all();
$records = array_values(array_filter($records, fn($r) =>
    ($filter === '' || $r['cctv_status'] === $filter) &&
    ($search === '' || stripos($r['business_name'] . ' ' . $r['owner_name'] . ' ' . $r['address'] . ' ' . $r['permit_no'], substr($search, 0, 100)) !== false)
));
