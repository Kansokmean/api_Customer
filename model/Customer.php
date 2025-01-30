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
            $arr = json_decode(file_get_contents(self::FILE_DATA), true);
            foreach ($arr as $cus) {
                if ($cus['email'] == $this->email) {
                    echo json_encode([
                        'result' => false,
                        'message' => 'Email already exists. Please use a different email.'
                    ]);
                    exit();
                }
            }
            $this->id = max(array_column($arr, "id")) + 1;
        }
        $fileName = null;
        if ($this->file) {
            if ($this->file['size'] > 1048576) {
                echo json_encode([
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
            $fileExtension = isset($path['extension']) ? $path['extension'] : 'jpg';
            $fileName = uniqid() . '.' . $fileExtension;
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
            'photo' => $this->file ? $this->photo : null
        ];
        array_push($arr, $temp);

        file_put_contents(self::FILE_DATA, json_encode($arr));

        return json_encode([
            'result' => true,
            'message' => 'Input data successfully!',
            'data' => $temp
        ]);
    }
    // public function index()
    // {
    //     $arr = [];
    //     if (file_exists(self::FILE_DATA)) {
    //         $arr = json_decode(file_get_contents(self::FILE_DATA, true));
    //     }

    //     return json_encode([
    //         'result' => true,
    //         'message' => 'Get data successfully.',
    //         'data' => $arr
    //     ]);
    // }
    public function index()
{
    $arr = [];
    if (file_exists(self::FILE_DATA)) {
        $arr = json_decode(file_get_contents(self::FILE_DATA), true);
        
        // Modify photo path for each customer
        foreach ($arr as &$cus) {
            if (!empty($cus['photo'])) {
                $cus['photo'] = self::DIR_PHOTO . $cus['photo'];
            }
        }
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
    public function DeletephotoById()
    {
        if (!file_exists(self::FILE_DATA)) {
            return json_encode([
                'result' => false,
                'message' => 'File data not found.'
            ]);
        }

        $data = json_decode(file_get_contents(self::FILE_DATA), true);
        $found = false;

        foreach ($data as $index => $item) {
            if ($item['id'] == $this->id) {
                if ($item['photo'] && file_exists($item['photo'])) {
                    unlink($item['photo']);
                    $data[$index]['photo'] = null;
                    $found = true;
                    break;
                }
            }
        }

        if (!$found) {
            return json_encode([
                'result' => false,
                'message' => 'Customer or photo not found.'
            ]);
        }

        file_put_contents(self::FILE_DATA, json_encode($data));

        return json_encode([
            'result' => true,
            'message' => 'Photo deleted successfully.'
        ]);
    }
    public function update()
    {
        if (!file_exists(self::FILE_DATA)) {
            return json_encode([
                'result' => false,
                'message' => 'File data not found.'
            ]);
        }

        $data = json_decode(file_get_contents(self::FILE_DATA), true);

        foreach ($data as $cus) {
            if ($cus['id'] != $this->id && $cus['email'] == $this->email) {
                return json_encode([
                    'result' => false,
                    'message' => 'Email already exists. Please use a different email.'
                ]);
            }
        }

        $filename = null;
        if ($this->file && isset($this->file['name']) && !empty($this->file['name'])) {
            $path = pathinfo($this->file['name']);
            $extension = isset($path['extension']) ? $path['extension'] : 'jpg';
            $filename = uniqid() . '.' . $extension;
            copy($this->file['tmp_name'], self::DIR_PHOTO . $filename);
            $this->photo = $filename;
        }

        $found = false;
        foreach ($data as $index => $item) {
            if ($item['id'] == $this->id) {
                $found = true;
                $data[$index]['fname'] = $this->fname;
                $data[$index]['lname'] = $this->lname;
                $data[$index]['gender'] = $this->gender;
                $data[$index]['branch'] = $this->branch;
                $data[$index]['email'] = $this->email;

                if ($this->file && !empty($filename)) {
                    if (!empty($item['photo']) && file_exists(self::DIR_PHOTO . $item['photo'])) {
                        unlink( self::DIR_PHOTO . $item['photo']);
                    }
                    $data[$index]['photo'] = $filename;
                } else {
                    $data[$index]['photo'] = $item['photo'];
                }

                break;
            }
        }

        if (!$found) {
            return json_encode([
                'result' => false,
                'message' => 'Customer not found.'
            ]);
        }

        file_put_contents(self::FILE_DATA, json_encode($data));

        return json_encode([
            'result' => true,
            'message' => 'Data updated successfully.'
        ]);
    }
    public function getchartData()
    {
        if (!file_exists(self::FILE_DATA)) {
            return json_encode([
                'result' => false,
                'message' => 'File data not found.'
            ]);
        }

        $data = json_decode(file_get_contents(self::FILE_DATA), true);
        $branchCount = array_count_values(array_column($data, 'branch'));
        $genderCount = array_count_values(array_column($data, 'gender'));

        $branchCountArray = array_values($branchCount);
        $genderCountArray = array_values($genderCount);

        return json_encode([
            'result' => true,
            'message' => 'Branch and gender count retrieved successfully.',
            'data' => [
                'branch' => $branchCountArray,
                'gender' => $genderCountArray
            ]
        ]);
    }
}
