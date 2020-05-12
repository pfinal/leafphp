<?php

namespace Util;

use Leaf\Application;
use Leaf\View;

/**
 * @see http://apidocjs.com/
 */
class Wrap
{
    private $baseUrl = 'http://git.it266.com/ethan/kushu/src/master/';

    private $ref;

    private $classDoc;

    private $methodDocs;

    public function __construct($className)
    {
        $this->ref = new \ReflectionClass($className);

        $this->classDoc = $this->parseClass(\Leaf\Util::parseDocCommentTags($this->ref));

        $this->getFile();

        //public方法
        $methods = $this->ref->getMethods(\ReflectionMethod::IS_PUBLIC);
        foreach ($methods as $method) {

            //跳过静态方法
            if ($method->isStatic()) {
                continue;
            }

            //跳过构造方法
            if ($method->isConstructor()) {
                continue;
            }

            $this->methodDocs[$method->name] = $this->parseMethod(\Leaf\Util::parseDocCommentTags($method));
        }
    }

    public function getFile()
    {
        $length = strlen(Application::$app['path']);
        $file = $this->ref->getFileName();
        return $this->baseUrl . substr($file, $length + 1);
    }

    public function getClassDoc()
    {
        return $this->classDoc;
    }

    public function getName($short = false)
    {
        if ($short) {
            //去掉namespace
            $name = rtrim(str_replace('\\', '/', $this->ref->name), '/\\');
            if (($pos = mb_strrpos($name, '/')) !== false) {
                $name = mb_substr($name, $pos + 1);
            }
            return $name;
        }
        return $this->ref->name;
    }

    public function getTitle()
    {
        $title = trim($this->classDoc['title']);

        return $title ? $title : $this->getShortName($this->getName());
    }

    public function getMethods()
    {
        return array_keys($this->methodDocs);
    }

    public function getMethodDocs()
    {
        return $this->methodDocs;
    }

    private function parseClass($doc)
    {
        $description = isset($doc['description']) ? trim($doc['description']) : '';
        unset($doc['description']);

        //取行第一个非空行作为标题
        $temp = explode("\n", $description);

        $title = '';
        while (count($temp) > 0 && $title == '') {
            $title = trim(array_shift($temp));
        }

        //余下的内容作为注解
        $comment = trim(join("\n", $temp));

        //解析markdown格式
        $parser = new \cebe\markdown\GithubMarkdown();
        $parser->html5 = true;
        $comment = $parser->parse($comment);

        return array_merge($doc, compact('title', 'comment'));
    }

    private function parseMethod($doc)
    {
        $doc = $this->parseClass($doc);

        $method = isset($doc['Method']) ? strtolower($doc['Method']) : 'any';
        unset($doc['Method']);

        $route = isset($doc['Route']) ? $doc['Route'] : '';
        unset($doc['Route']);

        //如果没有api，则自动生成
        if (!isset($doc['api']) && !empty($route)) {
            $doc['api'] = '{' . $method . '} ' . $route;
        }

        //处理api前缀
        if (isset($this->classDoc['apiPrefix']) && isset($doc['api']) && !empty($doc['api'])) {
            //"{any} front-api/init"  -->  "{any} app.php/front-api/init"
            if (preg_match('/(\{\w+\})(\s+)(.*)/', $doc['api'], $res)) {
                $doc['api'] = $res[1] . $res[2] . $this->classDoc['apiPrefix'] . $res[3];
            }
        }

        if (isset($doc['apiParam'])) {
            $doc['apiParam'] = (array)$doc['apiParam'];
        } else {
            $doc['apiParam'] = [];
        }

        //解析markdown格式
        $parser = new \cebe\markdown\GithubMarkdown();
        $parser->html5 = true;

        if (isset($doc['apiSuccess'])) {
            $doc['apiSuccess'] = $parser->parse($doc['apiSuccess']);
        } else {
            $doc['apiSuccess'] = '';
        }

        if (isset($doc['apiError'])) {
            $doc['apiError'] = $parser->parse($doc['apiError']);
        } else {
            $doc['apiError'] = '';
        }

        return $doc;
    }

    /**
     * @param $param
     * @return array
     *
     *  array:5 [
     *      "type" => "string"
     *      "field" => "username"
     *      "label" => "帐号"
     *      "description" => "有两种格式的帐号"
     *      "eg" => "summer@1001"
     *  ]
     */
    public function parseParam($param)
    {
        $result = $this->doParseParam($param);

        //解析markdown格式
        $parser = new \cebe\markdown\GithubMarkdown();
        $parser->html5 = true;
        $result['description'] = $parser->parse($result['description']);

        return $result;
    }

    /**
     * @param $param
     * @return array
     *
     *  array:5 [
     *      "type" => "string"
     *      "field" => "username"
     *      "label" => "帐号"
     *      "description" => "有两种格式的帐号"
     *      "eg" => "summer@1001"
     *  ]
     */
    private function doParseParam($param)
    {
        $return = [
            'type' => '',
            'field' => '',
            'label' => '',
            'description' => '',
            'eg' => '',
            'containerId' => '',
        ];

        //{string} username  帐号 有两种格式的帐号 eg. summer@1001, summer@gmail.com
        //$param = '{string} username  帐号 有两种格式的帐号 eg. summer@1001, summer@gmail.com';
        //$param = '{string} name      姓名     eg. 张三';
        //$param = '{string} name';

        //array:4 [
        //  0 => "{string} username  帐号 有两种格式的帐号 eg. summer@1001, summer@gmail.com"
        //  1 => "{string}"
        //  2 => "username"
        //  3 => "帐号 有两种格式的帐号 eg. summer@1001, summer@gmail.com"
        //]
        if (!preg_match('/\{(\w+)\}\s+([\w\-]+)(\s+(.*))?/s', $param, $result)) {
            //匹配不上时，全部作为field
            $return['field'] = $param;
            return $return;
        }

        $return['type'] = $result[1];
        $return['field'] = $result[2];
        $return['description'] = isset($result[4]) ? $result[4] : '';

        //从描述中，抽取label。 第一部份为label，剩下的放回description中
        if (!preg_match('/(.*?)\s+(.*)/s', $return['description'], $result)) {

            //整个作为label
            $return['label'] = $return['description'];
            $return['description'] = '';

            return $return;
        }

        $return['label'] = $result[1];
        $return['description'] = $result[2];
        //从描述中，抽取eg (eg.后面的部份)
        if (!preg_match('/(.*?)\s?eg\.\s+(.*)/s', $return['description'], $result)) {
            return $return;
        }

        $return['description'] = $result[1];
        $return['eg'] = $result[2];


        //提取containerId (生成测试代码)    "eg.1001 <userId>"
        if (preg_match('/(.*?)\s*<(\w+)>/s', $return['eg'], $result)) {
            $return['eg'] = $result[1];
            $return['containerId'] = $result[2];
        }

        return $return;
    }

    private function getShortName($className)
    {
        //去掉namespace
        $name = rtrim(str_replace('\\', '/', $className), '/\\');
        if (($pos = mb_strrpos($name, '/')) !== false) {
            $name = mb_substr($name, $pos + 1);
        }

        //去掉 Controller
        return str_replace('Controller', '', $name);
    }
}

/**
 * Api文档
 *
 * @apiPrefix app.php/
 * @version 1.0
 * @author 邹义良
 */
class Doc
{

    public static function makeDoc(array $controllers)
    {
        $list = [];
        foreach ($controllers as $controller) {
            $list[] = new Wrap($controller);
        }

        return View::render('doc.twig', compact( 'list'));
    }

    public static function buildTestCase(array $categories, $force = false)
    {
        $current = null;
        foreach ($categories as $item) {
            foreach ($item['class'] as $controller) {

                $build = new BuildTestCase(new Wrap($controller));

                $temp = str_replace('\\', '/', $controller);
                $temp = str_replace('//', '/', $temp);

                $build->write(Application::$app['path'] . '/tests/auto/' . $temp . 'Test.php', $force);
            }
        }


    }

//    /**
//     * 接口公共说明
//     *
//     * 采用 `Http` 协议，返回 `json` 数据格式
//     *
//     * 请求成功返回: `{status: true, data: "数据", code:"0" }`。 data 中的内容，根据接口不同，返回不同的数据
//     *
//     * 请求失败返回: `{status: false, data: "错误消息", code:"-1" }`。 失败时，data 中的内容为用户可读的错误消息，code中错误代码(string类型)，可用于程序识别，错误码含义见相关接口说明
//     *
//     * 公共请求参数: `token`  需要验证身份的接口需要传入此参数，调用接口时请替换URL中大写的TOKEN部份为你真实的`token`值，例如url为 `api/example?token=TOKEN`，实际请求地址为 `baseUrl + "api/example?token=" + "yourToken"`
//     *
//     * 当请求失败，错误码为 `INVALID_TOKEN` 时，说明token无效，请重新获取token，例如:
//     * `{status: false, data: "token无效", code:"INVALID_TOKEN" }`
//     *
//     */
//    public function demo1()
//    {
//
//    }

    /**
     * 接口文档示例
     *
     * 这是描述内容,这是描述内容,这是描述内容
     *
     * @api {post} api/user/create
     *
     * @apiParam {string} name  姓名  必填  2-10个长度 eg. ethan
     * @apiParam {int}    age   年龄  可选  默认值为18  eg. 20
     *
     * @apiSuccess
     *
     * ```
     * {
     *   "status": true,
     *   "data": [
     *     {
     *       "id": 1001,
     *       "name": "张三",
     *       "age": 19,
     *       "create_at": "2018-01-20 12:00:23"
     *     }
     *   ]
     * }
     * ```
     *
     * 字段说明
     *
     * |名称    | 类型 | 说明 |
     * | ----- |------| -----|
     * | id    | int    |  用户ID |
     * | name  | string |  姓名   |
     * | age   | int    |  年龄   |
     *
     * @apiError   `{status:false, data:"msg"}`
     *
     */
    public function demo2()
    {

    }
}

/**
 * 生成测试类
 * https://www.cnblogs.com/bourneli/archive/2012/09/08/2676978.html
 *
 * 依赖某个方法 @depends testTwo
 * 数据提供函数 @dataProvider provider
 *
 */
class BuildTestCase
{
    private $wrap;

    public function __construct(Wrap $wrap)
    {
        $this->wrap = $wrap;
    }

    public function write($file, $force = false)
    {
        $methodCodes = [];
        foreach ($this->wrap->getMethodDocs() as $method) {
            $methodCodes[] = $this->getMethodCode($method);
        }
        $code = $this->getClassCode($methodCodes);

        if (!$force && file_exists($file)) {
            echo "文件已存在: $file\n";
            return;
        }

        if (!file_exists(dirname($file))) {
            dump($file);
            dump(dirname($file));
            mkdir(dirname($file), 0777, true);
        }
        file_put_contents($file, $code);
    }

    private function getClassCode($methodCodes)
    {
        //生成命名空间
        $namespace = '';
        $arr = explode('\\', $this->wrap->getName());
        array_filter($arr);
        if (count($arr) > 1) {
            $arr = array_slice($arr, 0, count($arr) - 1);
            $namespace = '\\' . join('\\', $arr);
        }

        $data = [
            'namespace' => 'Test' . $namespace,
            'className' => $this->wrap->getName(true),
            'TestFileName' => str_replace('\\', '/', $this->wrap->getName()),
            'methodCodes' => $methodCodes,
            'classDoc' => $this->wrap->getClassDoc(),

        ];

        $code = <<<PHP
<?php

namespace {{namespace}};

use tests\TestApp;

/**
 * {{classDoc.title}} TestCase
 * ./vendor/bin/phpunit tests/{{TestFileName}}Test
 */
class {{className}}Test extends \BaseTestCase
{
{% for method in methodCodes %}
{{method|raw}}

{% endfor %}
}
PHP;
        return View::renderText($code, $data);
    }

    private function getMethodCode($method)
    {
        //参数
        $params = [];
        foreach ($method['apiParam'] as $param) {
            $param = $this->wrap->parseParam($param);
            //转义单引号
            $param['eg'] = str_replace('\'', '\\\'', $param['eg']);
            $params[] = $param;
        }

        $methodAndUrl = $this->getApi($method['api']);

        $data = [
            'api' => $method['api'],
            'methodAndUrl' => $methodAndUrl,
            'comment' => $method['title'],
            'method' => $this->apiToMethodName($methodAndUrl['url']),
            'params' => $params,
        ];


        $code = <<<PHP
    /**
     * {{comment|raw}}
     * {{api|raw}}
     */
    public function test{{method}}()
    {
{% if params is not empty %}
        \$data = [
{% for param in params %}
            '{{param.field}}' => {% if param.containerId is not empty %}TestApp::get('{{param.containerId}}'){% else %}'{{param.eg|default('')|raw}}'{%endif%},
{% endfor %}
        ];
{% if methodAndUrl.method == 'post' %}
        \$data = http_build_query(\$data);
{% endif %}
{% else %}
        \$data = null;
{% endif %}

        \$res = \$this->{{methodAndUrl.method}}('{{methodAndUrl.url|raw}}', \$data);
        
        //if(!\$res['status']){
        //    dump(\$res['data']);
        //}
        
        \$this->assertTrue(\$res['status']);
    }
PHP;

        return View::renderText($code, $data);
    }


    /**
     * @param string $str "{post} api/employee/client/register?token=xxx"
     * @return array
     *
     * [
     *  'method'=> 'post',
     *  'url' =>  "api/employee/client/register"
     * ]
     */
    public function getApi($str)
    {
        if (preg_match('/\{(\w+)\}\s+(.*)/', $str, $result)) {
            $method = $result[1];
            $url = $result[2];
        } else {
            $method = 'get';
            $url = $str;
        }

        $method = strtolower($method);
        if (in_array($method, ['any'])) {
            $method = 'get';
        }

        //去掉?后面
        $index = strpos($url, '?');
        if ($index !== false) {
            $url = substr($url, 0, $index);
        }

        return compact('method', 'url');
    }

    /**
     * @param string $str "api/employee/client-info/register"
     * @return string "ApiEmployeeClientInfoRegister"
     */
    private function apiToMethodName($str)
    {
        $str = str_replace('/', '_', $str);
        $str = str_replace('-', '_', $str);
        $str = str_replace('.', '_', $str);

        return $this->parseName($str, 1);
    }

    /**
     * 字符串命名风格转换
     * @param $name
     * @param int $type 0:将Java风格转换为C的风格  1:将C风格转换为Java的风格
     * @return string
     */
    private function parseName($name, $type = 0)
    {
        if ($type) {
            $name = preg_replace_callback("/_([a-zA-Z])/", function ($str) {
                return strtoupper($str[1]);
            }, $name);

            return ucfirst($name);

        } else {
            return strtolower(trim(preg_replace("/[A-Z]/", "_\\0", $name), "_"));
        }
    }
}