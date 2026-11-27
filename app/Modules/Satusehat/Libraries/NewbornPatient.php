<?php
namespace Modules\Satusehat\Libraries;
class NewbornPatient
{
    public function payload(array $input): array
    {
        $get = static function ($key, $max = 200) use ($input) {
            $v = $input[$key] ?? '';
            if (!is_string($v) || strlen(trim($v)) > $max) throw new \InvalidArgumentException('Isian ' . $key . ' tidak valid.');
            return trim($v);
        };
        $nik = $get('mother_nik', 16);
        if (!preg_match('/^\d{16}$/D', $nik)) throw new \InvalidArgumentException('NIK ibu harus 16 digit.');
        $name = $get('name'); $date = $get('birth_date', 10); $gender = $get('gender', 10);
        $parsed = \DateTimeImmutable::createFromFormat('!Y-m-d', $date);
        if (!$parsed || $parsed->format('Y-m-d') !== $date || $date > date('Y-m-d')) throw new \InvalidArgumentException('Tanggal lahir tidak valid.');
        if ($name === '' || !in_array($gender, ['male', 'female'], true)) throw new \InvalidArgumentException('Isi nama dan jenis kelamin bayi.');
        $order = filter_var($input['birth_order'] ?? null, FILTER_VALIDATE_INT);
        if ($order === false || $order < 0 || $order > 20) throw new \InvalidArgumentException('Urutan kelahiran tidak valid.');
        $address = ['use' => 'home', 'line' => [$get('address', 500)], 'city' => $get('city'), 'country' => 'ID'];
        if (!$address['line'][0]) throw new \InvalidArgumentException('Isi alamat lengkap.');
        if ($address['city'] === '') unset($address['city']);
        $extensions = [];
        foreach (['province' => 2, 'city_code' => 4, 'district' => 6, 'village' => 10] as $key => $length) {
            $value = $get($key, $length);
            if (!preg_match('/^\d{' . $length . '}$/D', $value)) throw new \InvalidArgumentException('Kode wilayah ' . $key . ' tidak valid.');
            $extensions[] = ['url' => $key === 'city_code' ? 'city' : $key, 'valueCode' => $value];
        }
        foreach (['rt', 'rw'] as $key) {
            $value = $get($key, 3);
            if ($value !== '') {
                if (!preg_match('/^\d{1,3}$/D', $value)) throw new \InvalidArgumentException('Isian ' . $key . ' tidak valid.');
                $extensions[] = ['url' => $key, 'valueCode' => $value];
            }
        }
        $address['extension'] = [['url' => 'https://fhir.kemkes.go.id/r4/StructureDefinition/administrativeCode', 'extension' => $extensions]];
        if ($get('postal_code', 10)) $address['postalCode'] = $get('postal_code', 10);
        $body = ['resourceType' => 'Patient', 'meta' => ['profile' => ['https://fhir.kemkes.go.id/r4/StructureDefinition/Patient']],
            'identifier' => [['use' => 'official', 'system' => 'https://fhir.kemkes.go.id/id/nik-ibu', 'value' => $nik]],
            'active' => true, 'name' => [['use' => 'official', 'text' => $name]], 'gender' => $gender, 'birthDate' => $date,
            'deceasedBoolean' => false, 'address' => [$address], 'multipleBirthInteger' => $order];
        $email = $get('email');
        if ($email !== '') {
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) throw new \InvalidArgumentException('Email tidak valid.');
            $body['telecom'] = [['system' => 'email', 'value' => $email, 'use' => 'home']];
        }
        $body['maritalStatus'] = ['coding' => [['system' => 'http://terminology.hl7.org/CodeSystem/v3-MaritalStatus', 'code' => 'M', 'display' => 'Married']], 'text' => 'Married'];
        $body['contact'] = [
            [
                'relationship' => [
                    ['coding' => [['system' => 'http://terminology.hl7.org/CodeSystem/v2-0131', 'code' => 'C']]],
                ],
                'name' => ['use' => 'official', 'text' => $get('mother_name')],
            ],
        ];
        if ($get('contact_phone', 30) !== '') $body['contact'][0]['telecom'] = [['system' => 'phone', 'value' => $get('contact_phone', 30), 'use' => 'mobile']];
        $body['communication'] = [['language' => ['coding' => [['system' => 'urn:ietf:bcp:47', 'code' => 'id-ID', 'display' => 'Indonesian']], 'text' => 'Indonesian'], 'preferred' => true]];
        if ($get('birth_place') !== '') $body['extension'][] = ['url' => 'https://fhir.kemkes.go.id/r4/StructureDefinition/birthPlace', 'valueAddress' => ['city' => $get('birth_place'), 'country' => 'ID']];
        $body['extension'][] = ['url' => 'https://fhir.kemkes.go.id/r4/StructureDefinition/citizenshipStatus', 'valueCode' => 'WNI'];
        return $body;
    }
}
