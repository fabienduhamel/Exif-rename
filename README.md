## Exif rename

⚠️ **Deprecated**: This project will not be maintained anymore as `exiftool '-FileName<${DateTimeOriginal}_${Model;s/ /-/g}%-c.%e' -d "%Y%m%d_%H%M%S" <file>` does it.

---

Renames all jpg/png files into a directory to a pattern based on exif data:

**testFile.jpg** becomes **20031214_120144_Canon-PowerShot-S40.jpg** (date + time + model).

### Installation

```
composer install
```

### Unit tests

```
./phpunit
```
