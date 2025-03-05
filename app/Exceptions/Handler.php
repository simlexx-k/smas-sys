public function register(): void
{
    $this->renderable(function (\Exception $e) {
        if ($e instanceof \Illuminate\Database\QueryException) {
            return redirect()->back()->with('error', 'Database error occurred. Please try again.');
        }
    });
}