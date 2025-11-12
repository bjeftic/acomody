const values = {};
const blankMetaTag = {
  content: null,
  hasAttribute() {
    return false;
  },
};
const extractConstant = (constant) => {
  const tag = document.head.querySelector('meta[name="' + constant + '"]') || blankMetaTag;
  // if tags content is json-encoded, decode it first
  let ret = tag.hasAttribute("data-is-json") ? JSON.parse(tag.content) : tag.content;
  switch (ret) {
    case "null":
      ret = null;
      break;
    case "true":
      ret = true;
      break;
    case "false":
      ret = false;
      break;
  }
  return ret;
};

extractConstant("runtimeConstList").forEach((c) => (values[c] = extractConstant(c)));

export default values;
