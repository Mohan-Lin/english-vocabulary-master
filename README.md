# English Vocabulary Master



![License](https://img.shields.io/badge/license-MIT-blue.svg)



![PHP](https://img.shields.io/badge/php-%3E%3D7.0-blue.svg)



![MySQL](https://img.shields.io/badge/mysql-%3E%3D5.7-blue.svg)



![Stars](https://img.shields.io/github/stars/linmohan/english-vocabulary-master.svg)

## 项目简介

**English Vocabulary Master** 是一个专为高中生设计的英语词汇学习与测试平台，特别针对高考英语 3500 词汇的掌握。作为一名高三学生，我深知词汇学习的重要性，因此开发了这个集实用性与创新性于一体的学习工具。

## 核心特色

### 🎯 专为高考设计



* **完整覆盖高考 3500 词汇**：基于最新高考大纲，确保词汇的权威性和实用性

* **教材同步学习**：按教材单元组织词汇，便于课堂同步学习

* **频率科学分级**：超高频、高频、中频、低频词汇智能分类

### 🧠 AI 智能辅助



* **智能答案分析**：集成 AI 技术，对答案进行深度分析和评价

* **个性化学习建议**：根据学习情况提供针对性的复习建议

* **错题智能管理**：自动记录错题，生成个性化复习方案

### 📊 数据驱动学习



* **详细学习统计**：记录学习进度，生成可视化学习报告

* **多维度分析**：从正确率、答题时间、错误类型等多角度分析学习情况

* **学习趋势追踪**：长期跟踪学习效果，见证词汇量的稳步提升

### 🎨 优秀用户体验



* **响应式设计**：完美适配 PC、平板、手机等多种设备

* **简洁界面**：专注学习本质，去除冗余干扰

* **流畅交互**：精心设计的动画效果，提升学习体验

## 主要功能

### 词汇测试模块



* **三种考查模式**：英译中、中译英、混合模式

* **智能筛选功能**：按单元、频率、黑体字等条件筛选

* **灵活测试设置**：可自定义测试数量、考查方向等参数

### 错题管理系统



* **自动记录错题**：智能识别错误答案并记录

* **错题专项练习**：针对错题进行集中训练

* **错题导出功能**：支持 CSV 格式导出，便于离线复习

### 学习数据分析



* **实时统计反馈**：即时显示答题正确率和用时

* **详细结果分析**：提供每道题的详细解析

* **学习报告生成**：定期生成学习总结报告

## 技术亮点

### 后端技术栈



* **PHP 7.0+**：稳定高效的服务器端语言

* **MySQL 5.7+**：可靠的关系型数据库

* **PDO 数据库操作**：安全的数据库访问方式

* **AJAX 异步交互**：提升用户体验的无刷新操作

### 前端技术栈



* **HTML5 + CSS3**：现代化的网页标准

* **JavaScript + jQuery**：丰富的交互功能

* **响应式设计**：适配各种屏幕尺寸

* **动画效果**：提升用户体验的视觉效果

### 特色技术



* **AI 集成**：阿里云 API 智能分析

* **数据缓存**：提升系统响应速度

* **安全防护**：完善的输入验证和错误处理

* **批量处理**：高效的大数据量处理能力

## 安装部署

### 环境要求



* PHP 7.0 或更高版本

* MySQL 5.7 或更高版本

* 支持 PDO 或 MySQLi 扩展

* 50MB 可用磁盘空间

### 快速开始



1. **克隆项目**



```
git clone https://github.com/Mohan-Lin/english-vocabulary-master.git

cd english-vocabulary-master
```



1. **配置数据库**



```
// config.php

define('DB\_HOST', 'localhost');

define('DB\_USER', '数据库用户名');

define('DB\_PASS', '数据库密码');

define('DB\_NAME', '数据库名');

define('ALIYUN\_API\_KEY', '阿里云API密钥'); // 可选
```



1. **初始化数据库**



```
\# 访问以下URL自动创建数据库表

http://yourdomain.com/install.php
```



1. **导入词汇数据**



```
\# 使用Python脚本批量导入词汇数据

python3 scripts/import\_words\_batch.py --host localhost --user username --password password --database dbname words.txt
```



1. **完成安装**



```
\# 删除install.php文件（重要！）

rm install.php
```

## 使用指南

### 基础使用流程



1. **开始测试**

* 访问系统首页

* 选择测试设置（词汇数量、考查方向等）

* 点击 "开始考查" 按钮

1. **答题过程**

* 根据提示输入答案

* 系统实时判断对错

* 可选择 "不知道" 跳过难题

1. **查看结果**

* 测试完成后查看详细结果

* 分析错题原因

* 参与错题专项练习

### 高级功能使用

#### 词汇导入格式



```
单词|音标|词性|释义|是否黑体|来源|频率

exchange|/ɪks'tʃeɪndʒ/|n./vt.|交换；交流；兑换|是|英语必修一 Welcome Unit|高频
```

#### 批量导入脚本对比



```
\# 标准版（智能更新）- 适合日常维护

python3 scripts/import\_words.py --host localhost --user username --password password --database dbname words.txt

\# 并发版（高速导入）- 适合大批量更新

python3 scripts/import\_words\_concurrent.py --host localhost --user username --password password --database dbname --threads 8 words.txt

\# 批量版（首次导入）- 适合首次大量数据导入

python3 scripts/import\_words\_batch.py --host localhost --user username --password password --database dbname --batch-size 5000 words.txt
```

## 项目结构



```
english-vocabulary-master/

├── config.php              # 配置文件

├── functions.php           # 核心功能函数

├── index.php               # 首页

├── exam.php                # 测试页面

├── result.php              # 结果页面

├── review.php              # 错题页面

├── submit\_answer.php       # 答案提交处理

├── install.php             # 安装脚本

├── changelog.php           # 更新日志

├── css/

│   └── style.css           # 样式文件

├── scripts/

│   ├── import\_words.py     # Python导入脚本

│   ├── import\_words\_concurrent.py

│   └── import\_words\_batch.py

├── screenshots/            # 截图目录

├── README.md               # 项目说明

└── LICENSE                 # 许可证
```

## 界面展示

### 系统首页



![系统首页](https://en.linmohan.top/css/homepage.png)

### 测试界面



![测试界面](https://en.linmohan.top/css/exam.png)

### 结果分析



![结果分析](https://en.linmohan.top/css/result.png)

### 错题复习



![错题复习](https://en.linmohan.top/css/review.png)

## 开发背景

作为一名高三学生，我在备考过程中深深感受到了词汇学习的重要性和挑战性。传统的词汇学习方法往往效率低下，缺乏针对性和趣味性。因此，我决定利用自己的编程技能，开发一个专门针对高中生的英语词汇学习平台。

这个项目不仅是我的个人作品，更是我对教育技术的探索和实践。通过将 AI 技术、数据分析与英语学习相结合，我希望能够为更多的同学提供高效、有趣的学习工具。

## 技术挑战与解决方案

### 挑战一：大量词汇数据的高效处理

**解决方案**：开发了三种不同的导入脚本，分别针对不同的数据量和使用场景，实现了 10-20 倍的性能提升。

### 挑战二：AI 分析的准确性和响应速度

**解决方案**：优化了 AI 提示词设计，添加了结果缓存机制，在保证准确性的同时大幅提升了响应速度。

### 挑战三：跨设备的用户体验一致性

**解决方案**：采用响应式设计理念，确保在 PC、平板、手机等各种设备上都能获得良好的使用体验。

### 挑战四：数据安全和隐私保护

**解决方案**：实现了完善的输入验证、错误处理和数据加密机制，确保用户数据的安全。

## 未来规划

### 短期目标（1-3 个月）

完善移动端用户体验

添加更多的词汇库和学习资源

优化 AI 分析算法，提高准确性

增加社交功能，支持学习小组

### 中期目标（3-6 个月）

开发移动端 APP

集成更多的学习功能（听力、阅读等）

实现个性化学习路径推荐

支持多语言界面

### 长期目标（6 个月以上）

构建完整的在线学习平台

集成机器学习算法，实现智能学习推荐

支持多种学科的学习功能

建立学习社区，促进知识分享

## 贡献指南

欢迎所有对教育技术和英语学习感兴趣的开发者参与项目开发！

### 如何贡献



1. **Bug 报告**

* 详细描述问题现象和复现步骤

* 提供相关的错误截图和日志信息

* 在 Issue 中清晰地表达您的观点

1. **功能建议**

* 说明功能的必要性和使用场景

* 提供具体的实现思路和设计方案

* 考虑功能的兼容性和扩展性

1. **代码贡献**

* Fork 项目到您的 GitHub 账号

* 创建特性分支（feature/xxx）

* 提交代码并创建 Pull Request

* 等待代码审查和合并

### 开发规范



* 遵循 PSR-2 代码规范

* 保持代码的可读性和可维护性

* 为新功能添加详细的测试用例

* 更新相关的文档和注释

## 技术支持

如果您在使用过程中遇到问题，欢迎通过以下方式联系我：



* **项目主页**：[https://github.com/Mohan-Lin/english-vocabulary-master](https://github.com/Mohan-Lin/english-vocabulary-master)

* **问题反馈**：[https://github.com/Mohan-Lin/english-vocabulary-master/issues](https://github.com/Mohan-Lin/english-vocabulary-master/issues)

* **个人博客**：[https://www.linmohan.top](https://www.linmohan.top)

* **邮箱联系**：admin@shaoyunb.top|admin@9876111.xyz

## 许可证

本项目采用 [MIT License](LICENSE) 开源协议。

## 致谢

感谢所有支持和帮助过这个项目的老师、同学和开发者们！

特别感谢：


* 同学们在测试过程中提供的宝贵反馈

* 开源社区的技术分享和支持

* 阿里云提供的 AI 技术支持

## Star History

如果这个项目对您有帮助，欢迎给个 Star 支持一下！



![Star History Chart](https://api.star-history.com/svg?repos=linmohan/english-vocabulary-master\&type=Date)



***

**用技术改变学习，让教育更加智能！** 🚀

**Made with ❤️ by 林默涵 from 南平高级中学**
