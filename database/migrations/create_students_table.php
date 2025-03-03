use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStudentsTable extends Migration
{
    public function up()
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('admission_number')->unique();
            $table->foreignId('class_id')->constrained('classes')->onDelete('cascade');
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });

        Schema::table('students', function (Blueprint $table) {
            // If the column doesn't exist, add it
            if (!Schema::hasColumn('students', 'class_id')) {
                $table->foreignId('class_id')->constrained('classes')->onDelete('cascade');
            }
        });
    }

    public function down()
    {
        Schema::dropIfExists('students');
    }
} 