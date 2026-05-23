php artisan tinker

use App\Models\User;
$user = User::find(2); // ID del usuario
$user->is_admin = true;
$user->save();
exit;