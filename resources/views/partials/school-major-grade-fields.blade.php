@php
    $schools = $schools ?? config('schools', []);
    $selectedSchool = old('school', $selectedSchool ?? '');
    $selectedMajor = old('major', $selectedMajor ?? '');
    $selectedGrade = old('grade', $selectedGrade ?? '');
    $selectedParallel = old('parallel', $selectedParallel ?? '');
    $inputClass = $inputClass ?? 'w-full rounded-2xl border-slate-200 dark:border-white/10 bg-slate-50 dark:bg-white/5 px-5 py-4 focus:border-cyan-500 focus:ring-cyan-500';
    $labelClass = $labelClass ?? 'block mb-2 font-bold';
    $required = $required ?? true;
    $showMajor = $showMajor ?? true;
    $showClassFields = $showClassFields ?? true;
@endphp

<div>
    <label for="school" class="{{ $labelClass }}">Sekolah</label>
    <select id="school" name="school" @required($required) class="{{ $inputClass }}">
        <option value="">Pilih sekolah</option>
        @foreach ($schools as $schoolName => $majors)
            <option value="{{ $schoolName }}" @selected($selectedSchool === $schoolName)>
                {{ $schoolName }}
            </option>
        @endforeach
    </select>
    @error('school')
        <p class="mt-2 text-sm text-red-500">{{ $message }}</p>
    @enderror
</div>

@if ($showMajor)
<div>
    <label for="major" class="{{ $labelClass }}">Jurusan</label>
    <select id="major" name="major" @required($required) class="{{ $inputClass }}">
        <option value="">Pilih sekolah terlebih dahulu</option>
    </select>
    @error('major')
        <p class="mt-2 text-sm text-red-500">{{ $message }}</p>
    @enderror
</div>
@endif

@if ($showClassFields)
<div>
    <label for="grade" class="{{ $labelClass }}">Kelas</label>
    <select id="grade" name="grade" @required($required) class="{{ $inputClass }}">
        <option value="">Pilih kelas</option>
        @foreach (['X', 'XI', 'XII', 'S1'] as $grade)
            <option value="{{ $grade }}" @selected($selectedGrade === $grade)>
                {{ $grade }}
            </option>
        @endforeach
    </select>
    @error('grade')
        <p class="mt-2 text-sm text-red-500">{{ $message }}</p>
    @enderror
</div>

<div>
    <label for="parallel" class="{{ $labelClass }}">Paralel</label>
    <select id="parallel" name="parallel" @required($required) class="{{ $inputClass }}">
        <option value="">Pilih paralel</option>
        @foreach (['A', 'B', 'C', 'D'] as $parallel)
            <option value="{{ $parallel }}" @selected($selectedParallel === $parallel)>
                {{ $parallel }}
            </option>
        @endforeach
    </select>
    @error('parallel')
        <p class="mt-2 text-sm text-red-500">{{ $message }}</p>
    @enderror
</div>
@endif

<script>
    (function () {
        const schools = @json($schools);
        const schoolSelect = document.getElementById('school');
        const majorSelect = document.getElementById('major');
        const selectedMajor = @json($selectedMajor);

        if (!schoolSelect || !majorSelect) {
            return;
        }

        function fillMajors(schoolName, currentMajor) {
            const majors = schools[schoolName] || [];
            majorSelect.innerHTML = '';

            const placeholder = document.createElement('option');
            placeholder.value = '';
            placeholder.textContent = majors.length ? 'Pilih jurusan' : 'Pilih sekolah terlebih dahulu';
            majorSelect.appendChild(placeholder);

            majors.forEach(function (major) {
                const option = document.createElement('option');
                option.value = major;
                option.textContent = major;
                if (currentMajor && currentMajor === major) {
                    option.selected = true;
                }
                majorSelect.appendChild(option);
            });
        }

        schoolSelect.addEventListener('change', function () {
            fillMajors(schoolSelect.value, '');
        });

        if (schoolSelect.value) {
            fillMajors(schoolSelect.value, selectedMajor);
        }
    })();
</script>
