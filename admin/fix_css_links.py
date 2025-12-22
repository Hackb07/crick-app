import os

target_dir = r"e:\xampp\htdocs\cricapp\admin"
search_pattern = 'href="<?= assetUrl(\'css/admin-enhanced.css\') ?>`n    <link rel="stylesheet" href="<?= assetUrl(\'css/admin-fixes.css\') ?>">">'
replace_pattern = 'href="<?= assetUrl(\'css/admin-enhanced.css\') ?>">\n    <link rel="stylesheet" href="<?= assetUrl(\'css/admin-fixes.css\') ?>">'

count = 0
for root, dirs, files in os.walk(target_dir):
    for file in files:
        if file.endswith(".php"):
            file_path = os.path.join(root, file)
            try:
                with open(file_path, 'r', encoding='utf-8') as f:
                    content = f.read()
                
                if search_pattern in content:
                    new_content = content.replace(search_pattern, replace_pattern)
                    with open(file_path, 'w', encoding='utf-8') as f:
                        f.write(new_content)
                    print(f"Fixed: {file_path}")
                    count += 1
            except Exception as e:
                print(f"Error processing {file_path}: {e}")

print(f"Total files fixed: {count}")
