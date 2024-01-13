#!/usr/bin/env bash

# 如果命令失败或使用了未初始化的变量，退出脚本
set -euo pipefail

# ==================================
# 验证仓库是否干净
# ==================================

# 列出未提交的更改，并检查输出是否为空
if [ -n "$(git status --porcelain)" ]; then
# 打印错误消息
printf "\\n错误：仓库中有未提交的更改\\n\\n"
# 以错误代码退出
exit 1
fi

# ==================================
# 从 Git 标签获取最新版本
# ==================================

# 按语义版本排序列出 Git 标签
GIT_TAGS=$(git tag --sort=version:refname)

# 获取输出的最后一行，它返回最后一个标签（最新版本）
GIT_TAG_LATEST=$(echo "$GIT_TAGS" | tail -n 1)

# 如果没有找到标签，默认为 v0.0.0
if [ -z "$GIT_TAG_LATEST" ]; then
  GIT_TAG_LATEST="v0.0.0"
fi

# 从标签中删除前缀 'v' 以便轻松递增
GIT_TAG_LATEST=$(echo "$GIT_TAG_LATEST" | sed 's/^v//')

# ==================================
# 递增版本号
# ==================================

# 从传递给脚本的第一个参数获取版本类型
VERSION_TYPE="${1-}"
VERSION_NEXT=""

if [ "$VERSION_TYPE" = "+" ]; then
# 递增修订版本
  VERSION_NEXT="$(echo "$GIT_TAG_LATEST" | awk -F. '{$NF++; print $1"."$2"."$NF}')"
elif [ "$VERSION_TYPE" = "+2" ]; then
# 递增次要版本
  VERSION_NEXT="$(echo "$GIT_TAG_LATEST" | awk -F. '{$2++; $3=0; print $1"."$2"."$3}')"
elif [ "$VERSION_TYPE" = "+1" ]; then
# 递增主要版本
  VERSION_NEXT="$(echo "$GIT_TAG_LATEST" | awk -F. '{$1++; $2=0; $3=0; print $1"."$2"."$3}')"
else
# 为未知的版本类型打印错误
printf "\\n错误：传递的 VERSION_TYPE 参数无效，必须是 '+'、'+2' 或 '+1'\\n\\n"
# 以错误代码退出
exit 1
fi

# ==================================
# 更新清单文件中的版本号（可选）
# ==================================

# 更新 ver.json 中的版本
sed -i "s/\"version\": \".*\"/\"version\": \"$VERSION_NEXT\"/" composer.json

# 提交更改
git add .
git commit -m "Tag： v$VERSION_NEXT"

# ==================================
# 为新版本创建 Git 标签
# ==================================

# 创建带注释的标签
git tag -a "v$VERSION_NEXT" -m "Tag： v$VERSION_NEXT"

# 可选：将提交和标签推送到远程
git push --tags
