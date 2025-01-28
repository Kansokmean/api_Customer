<?php
class Customer
{
    public $id, $fname, $lname, $gender, $branch, $email, $photo;
    public $file = null;
    public const FILE_DATA = '../../storage/data/customer.json';
    public const DIR_PHOTO = '../../storage/photo/customer/';
    public function store()
    {
        $arr = [];
        $this->id = 1;
        if (!is_dir('../../storage')) {
            mkdir('../../storage');
        }
        if (!is_dir('../../storage/photo')) {
            mkdir('../../storage/photo');
        }
        if (!is_dir('../../storage/photo/customer')) {
            mkdir('../../storage/photo/customer');
        }
        if (!is_dir('../../storage/data')) {
            mkdir('../../storage/data');
        }
        if (file_exists(self::FILE_DATA)) {
            $arr = json_decode(file_get_contents(self::FILE_DATA, true));
            $this->id = max(array_column($arr, "id")) + 1;
        }
        $fileName = null;
        if ($this->file) {
            if ($this->file['size'] > 1048576) {
                echo json_encode(value: [
                    'result' => false,
                    'message' => 'Max file image size is 1MB.'
                ]);
                exit();
            }
            $imgExtion = ['image/jpeg', 'image/png'];
            if (!in_array($this->file['type'], $imgExtion)) {
                echo json_encode([
                    'result' => false,
                    'message' => 'File image must be png or jpeg.'
                ]);
                exit();
            }

            $path = pathinfo($this->file['name']);
            $fileName = uniqid() . '.' . (strlen($path['extension']) == 0 ? 'jpg' : $path['extension']);
            copy($this->file['tmp_name'], self::DIR_PHOTO . $fileName);
            $this->photo = $fileName;
        }

        $temp = [
            'id' => $this->id,
            'fname' => $this->fname,
            'lname' => $this->lname,
            'gender' => $this->gender,
            'branch' => $this->branch,
            'email' => $this->email,
            'photo' => $this->file ? self::DIR_PHOTO . $this->photo : null
        ];
        array_push($arr, $temp);

        file_put_contents(self::FILE_DATA, json_encode($arr));

        return json_encode([
            'result' => true,
            'message' => 'Input data successfully!',
            'data' => $temp
        ]);
    }
    public function index()
    {
        $arr = [];
        if (file_exists(self::FILE_DATA)) {
            $arr = json_decode(file_get_contents(self::FILE_DATA, true));
        }

        return json_encode([
            'result' => true,
            'message' => 'Get data successfully.',
            'data' => $arr
        ]);
    }
    public function destroy()
    {
        $found = false;
        if (!file_exists(self::FILE_DATA)) {
            return json_encode([
                'result' => false,
                'message' => 'File data not found.'
            ]);
        }
        $arr = json_decode(file_get_contents(self::FILE_DATA), true);
        foreach ($arr as $index => $item) {
            if ($item['id'] == $this->id) {
                $found = true;
                if ($item['photo'] && file_exists($item['photo'])) {
                    unlink($item['photo']);
                }
                array_splice($arr, $index, 1);
                break;
            }
        }
        if (!$found) {
            echo json_encode([
                'result' => false,
                'message' => 'Data not found.'
            ]);
            exit();
        }
        if (count($arr) == 0) {
            unlink(self::FILE_DATA);
        } else {
            file_put_contents(self::FILE_DATA, json_encode($arr));
        }
        return json_encode([
            'result' => true,
            'message' => 'Succesfully delete data.'
        ]);
    }
    public function update()
    {
        if (!file_exists(self::FILE_DATA)) {
            return json_encode([
                'result' => false,
                'message' => 'File data not found.'
            ]);
        } else {
            $data = json_decode(file_get_contents(self::FILE_DATA), true);

            $filename = null;
            if ($this->file) {
                $path = pathinfo($this->file['name']);
                $filename = uniqid() . '.' . (strlen($path['extension']) == 0 ? 'jpg' : $path['extension']);
                copy($this->file['tmp_name'], self::DIR_PHOTO . $filename);
            }
            $this->photo = $filename;

            $stu = null;
            $found = false;
            foreach ($data as $index => $item) {
                if ($item['id'] == $this->id) {
                    $found = true;
                    $data[$index]['fname'] = $this->fname;
                    $data[$index]['lname'] = $this->lname;
                    $data[$index]['gender'] = $this->gender;
                    $data[$index]['branch'] = $this->branch;
                    $data[$index]['email'] = $this->email;
                    if ($this->file) {
                        if ($item['photo'] && file_exists($item['photo'])) {
                            unlink($item['photo']);
                        }
                        $data[$index]['photo'] = self::DIR_PHOTO . $this->photo;
                    } else {
                        $data[$index]['photo'] = $item['photo'];
                    }
                    $stu = $data[$index];
                    break;
                }
            }
            if (!$found) {
                echo json_encode([
                    'result' => false,
                    'message' => 'Customer not found.'
                ]);
                exit();
            } else {
                file_put_contents(self::FILE_DATA, json_encode($data));
                return json_encode([
                    'result' => true,
                    'data' => $stu,
                    'message' => 'Data updated successfully.'
                ]);
            }
        }
    }
}
