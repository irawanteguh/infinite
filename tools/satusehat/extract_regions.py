"""Extract the supplied Kemendagri workbook into validated master-region CSV."""
import csv
import hashlib
import json
import sys
import zipfile
import xml.etree.ElementTree as ET
from collections import Counter

source, output = sys.argv[1:3]
ns = {'s': 'http://schemas.openxmlformats.org/spreadsheetml/2006/main'}
regions = {}
source_rows = 0
with zipfile.ZipFile(source) as archive:
    strings = [''.join(node.itertext()) for node in ET.fromstring(archive.read('xl/sharedStrings.xml'))]
    with archive.open('xl/worksheets/sheet1.xml') as sheet:
        for _, row in ET.iterparse(sheet, events=['end']):
            if row.tag != '{' + ns['s'] + '}row':
                continue
            values = {}
            for cell in row:
                value = cell.find('s:v', ns)
                text = value.text if value is not None else ''.join(cell.itertext())
                if cell.get('t') == 's':
                    text = strings[int(text)]
                values[''.join(c for c in cell.get('r', '') if c.isalpha())] = text or ''
            if row.get('r') == '1':
                expected = ['kode_kelurahan', 'nama_keluarahan', 'kode_kecamatan', 'nama_kecamatan', 'kode_kabko', 'nama_kabko', 'kode_provinsi', 'nama_provinsi', 'code_system']
                if [values.get(c) for c in 'ABCDEFGHI'] != expected:
                    raise ValueError('Workbook headers do not match the expected source structure')
                row.clear()
                continue
            if not any(values.values()):
                row.clear()
                continue
            source_rows += 1
            for code_col, name_col, parent_col, level, length in [('G', 'H', None, 'provinces', 2), ('E', 'F', 'G', 'cities', 4), ('C', 'D', 'E', 'districts', 6), ('A', 'B', 'C', 'sub-districts', 10)]:
                code, name = values[code_col], values[name_col]
                parent = values[parent_col] if parent_col else ''
                if len(code) != length or not code.isascii() or not code.isdigit() or (parent and not code.startswith(parent)):
                    raise ValueError(f'Invalid region at source row {row.get("r")}: {code}')
                item = (code, parent, level, name, values['I'])
                if code in regions and regions[code] != item:
                    raise ValueError(f'Conflicting source records for region {code}: {regions[code]} vs {item}')
                regions[code] = item
            row.clear()
for code, parent, *_ in regions.values():
    if parent and parent not in regions:
        raise ValueError(f'Missing parent {parent} for {code}')
with open(output, 'w', newline='', encoding='utf-8') as target:
    writer = csv.writer(target)
    writer.writerow(['code', 'parent_code', 'level', 'name', 'code_system'])
    writer.writerows(sorted(regions.values(), key=lambda item: (len(item[0]), item[0])))
print(json.dumps({'source_rows': source_rows, 'counts': dict(Counter(row[2] for row in regions.values())), 'total': len(regions), 'missing_names': dict(Counter(row[2] for row in regions.values() if not row[3].strip())), 'source_sha256': hashlib.sha256(open(source, 'rb').read()).hexdigest()}))
