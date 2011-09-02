// test toolbar definition, load all defs into toolbars static params

MEE.Toolbar.defs['test'] = {
    home: { label: "Home", items: [
        { name: "Some Text", image: "image.png" },
        { name: "Some Text", image: "image.png" },
        { name: "Some Text", image: "image.png" },
    ]
    },
    tabs: [
        { name: "Tab 1", id: 'tab1', panes:
            [
                { name: "Symbols", type: "icons", width: '280px', items: [
                    { desc: "PlusMinus", latex: "/sum", text: "±" },
                    { desc: "PlusMinus", latex: "/sum", text: "∞" },
                    { desc: "PlusMinus", latex: "/sum", text: "=" },
                    { desc: "PlusMinus", latex: "/sum", text: "≠" },
                    { desc: "PlusMinus", latex: "/sum", text: "~" },
                    { desc: "PlusMinus", latex: "/sum", text: "×" },
                    { desc: "PlusMinus", latex: "/sum", text: "÷" },
                    { desc: "PlusMinus", latex: "/sum", text: "!" },
                ]
                },
                { name: "Pane 2", type: "menus", items: [
                    { text: "Fraction", image: "toolbar/icons/faction.png", sections: [
                        { heading: "Heading 1", items: [
                            { text: "Sum", image: "toolbar/icons/sum-64.png", latex: "/prod" },
                            { text: "", image: "toolbar/icons/sum-64.png", latex: "/prod" },
                            { text: "Another", image: "toolbar/icons/product.png", latex: "/prod" },
                            { text: "Sum", image: "toolbar/icons/sum-64.png", latex: "/prod" },
                            { text: "", image: "toolbar/icons/sum-64.png", latex: "/prod" },
                            { text: "Another", image: "toolbar/icons/product.png", latex: "/prod" },
                            { text: "Sum", image: "toolbar/icons/sum-64.png", latex: "/prod" },
                            { text: "", image: "toolbar/icons/sum-64.png", latex: "/prod" },
                            { text: "Another", image: "toolbar/icons/product.png", latex: "/prod" },
                            { text: "Sum", image: "toolbar/icons/sum-64.png", latex: "/prod" },
                            { text: "", image: "toolbar/icons/sum-64.png", latex: "/prod" },
                            { text: "Another", image: "toolbar/icons/product.png", latex: "/prod" },
                            { text: "Sum", image: "toolbar/icons/sum-64.png", latex: "/prod" },
                            { text: "", image: "toolbar/icons/sum-64.png", latex: "/prod" },
                            { text: "Another", image: "toolbar/icons/product.png", latex: "/prod" },
                        ]
                        },
                        { heading: "Heading 2", items: [
                            { text: "Sum", image: "toolbar/icons/sum-64.png", latex: "/prod" },
                            { text: "", image: "toolbar/icons/sum-64.png", latex: "/prod" },
                            { text: "Another", image: "toolbar/icons/product.png", latex: "/prod" },
                            { text: "Sum", image: "toolbar/icons/sum-64.png", latex: "/prod" },
                            { text: "", image: "toolbar/icons/sum-64.png", latex: "/prod" },
                            { text: "Another", image: "toolbar/icons/product.png", latex: "/prod" },
                            { text: "Sum", image: "toolbar/icons/sum-64.png", latex: "/prod" },
                            { text: "", image: "toolbar/icons/sum-64.png", latex: "/prod" },
                            { text: "Another", image: "toolbar/icons/product.png", latex: "/prod" },
                            { text: "Sum", image: "toolbar/icons/sum-64.png", latex: "/prod" },
                            { text: "", image: "toolbar/icons/sum-64.png", latex: "/prod" },
                        ]
                        },
                    ]
                    },
                    { text: "Script", image: "toolbar/icons/script.png", sections: [
                        { heading: "Heading 1", items: [
                            { text: "Sum", image: "toolbar/icons/sum-64.png", latex: "/prod" },
                            { text: "", image: "toolbar/icons/sum-64.png", latex: "/prod" },
                            { text: "Another", image: "toolbar/icons/product.png", latex: "/prod" },
                            { text: "Sum", image: "toolbar/icons/sum-64.png", latex: "/prod" },
                            { text: "", image: "toolbar/icons/sum-64.png", latex: "/prod" },
                            { text: "Another", image: "toolbar/icons/product.png", latex: "/prod" },
                            { text: "Sum", image: "toolbar/icons/sum-64.png", latex: "/prod" },
                            { text: "", image: "toolbar/icons/sum-64.png", latex: "/prod" },
                            { text: "Another", image: "toolbar/icons/product.png", latex: "/prod" },
                            { text: "Sum", image: "toolbar/icons/sum-64.png", latex: "/prod" },
                            { text: "", image: "toolbar/icons/sum-64.png", latex: "/prod" },
                            { text: "Another", image: "toolbar/icons/product.png", latex: "/prod" },
                            { text: "Sum", image: "toolbar/icons/sum-64.png", latex: "/prod" },
                            { text: "", image: "toolbar/icons/sum-64.png", latex: "/prod" },
                            { text: "Another", image: "toolbar/icons/product.png", latex: "/prod" },
                        ]
                        },
                        { heading: "Heading 2", items: [
                            { text: "Sum", image: "toolbar/icons/sum-64.png", latex: "/prod" },
                            { text: "", image: "toolbar/icons/sum-64.png", latex: "/prod" },
                            { text: "Another", image: "toolbar/icons/product.png", latex: "/prod" },
                            { text: "Sum", image: "toolbar/icons/sum-64.png", latex: "/prod" },
                            { text: "", image: "toolbar/icons/sum-64.png", latex: "/prod" },
                            { text: "Another", image: "toolbar/icons/product.png", latex: "/prod" },
                            { text: "Sum", image: "toolbar/icons/sum-64.png", latex: "/prod" },
                            { text: "", image: "toolbar/icons/sum-64.png", latex: "/prod" },
                            { text: "Another", image: "toolbar/icons/product.png", latex: "/prod" },
                            { text: "Sum", image: "toolbar/icons/sum-64.png", latex: "/prod" },
                            { text: "", image: "toolbar/icons/sum-64.png", latex: "/prod" },
                            { text: "Another", image: "toolbar/icons/product.png", latex: "/prod" },
                            { text: "Sum", image: "toolbar/icons/sum-64.png", latex: "/prod" },
                            { text: "", image: "toolbar/icons/sum-64.png", latex: "/prod" },
                            { text: "Another", image: "toolbar/icons/product.png", latex: "/prod" },
                        ]
                        },
                    ]
                    },
                    { text: "Radical", image: "toolbar/icons/radical.png", sections: [
                        { heading: "Heading 1", items: [
                            { text: "Sum", image: "toolbar/icons/sum-64.png", latex: "/prod" },
                            { text: "", image: "toolbar/icons/sum-64.png", latex: "/prod" },
                            { text: "Another", image: "toolbar/icons/product.png", latex: "/prod" },
                            { text: "Sum", image: "toolbar/icons/sum-64.png", latex: "/prod" },
                            { text: "", image: "toolbar/icons/sum-64.png", latex: "/prod" },
                            { text: "Another", image: "toolbar/icons/product.png", latex: "/prod" },
                            { text: "Sum", image: "toolbar/icons/sum-64.png", latex: "/prod" },
                            { text: "", image: "toolbar/icons/sum-64.png", latex: "/prod" },
                            { text: "Another", image: "toolbar/icons/product.png", latex: "/prod" },
                            { text: "Sum", image: "toolbar/icons/sum-64.png", latex: "/prod" },
                            { text: "", image: "toolbar/icons/sum-64.png", latex: "/prod" },
                            { text: "Another", image: "toolbar/icons/product.png", latex: "/prod" },
                            { text: "Sum", image: "toolbar/icons/sum-64.png", latex: "/prod" },
                            { text: "", image: "toolbar/icons/sum-64.png", latex: "/prod" },
                            { text: "Another", image: "toolbar/icons/product.png", latex: "/prod" },
                        ]
                        },
                        { heading: "Heading 2", items: [
                            { text: "Sum", image: "toolbar/icons/sum-64.png", latex: "/prod" },
                            { text: "", image: "toolbar/icons/sum-64.png", latex: "/prod" },
                            { text: "Another", image: "toolbar/icons/product.png", latex: "/prod" },
                            { text: "Sum", image: "toolbar/icons/sum-64.png", latex: "/prod" },
                            { text: "", image: "toolbar/icons/sum-64.png", latex: "/prod" },
                            { text: "Another", image: "toolbar/icons/product.png", latex: "/prod" },
                            { text: "Sum", image: "toolbar/icons/sum-64.png", latex: "/prod" },
                            { text: "", image: "toolbar/icons/sum-64.png", latex: "/prod" },
                            { text: "Another", image: "toolbar/icons/product.png", latex: "/prod" },
                            { text: "Sum", image: "toolbar/icons/sum-64.png", latex: "/prod" },
                            { text: "", image: "toolbar/icons/sum-64.png", latex: "/prod" },
                            { text: "Another", image: "toolbar/icons/product.png", latex: "/prod" },
                            { text: "Sum", image: "toolbar/icons/sum-64.png", latex: "/prod" },
                            { text: "", image: "toolbar/icons/sum-64.png", latex: "/prod" },
                            { text: "Another", image: "toolbar/icons/product.png", latex: "/prod" },
                        ]
                        },
                    ]
                    },
                ]
                },
                { name: "Pane 3", type: "hmenus", width: '90px', items: [
                    { text: "Fraction", image: "toolbar/icons/faction-16.png", sections: [
                        { heading: "Heading 1", items: [
                            { text: "Sum", image: "toolbar/icons/sum-64.png", latex: "/prod" },
                            { text: "", image: "toolbar/icons/sum-64.png", latex: "/prod" },
                            { text: "Another", image: "toolbar/icons/product.png", latex: "/prod" },
                            { text: "Sum", image: "toolbar/icons/sum-64.png", latex: "/prod" },
                            { text: "", image: "toolbar/icons/sum-64.png", latex: "/prod" },
                            { text: "Another", image: "toolbar/icons/product.png", latex: "/prod" },
                            { text: "Sum", image: "toolbar/icons/sum-64.png", latex: "/prod" },
                            { text: "", image: "toolbar/icons/sum-64.png", latex: "/prod" },
                            { text: "Another", image: "toolbar/icons/product.png", latex: "/prod" },
                            { text: "Sum", image: "toolbar/icons/sum-64.png", latex: "/prod" },
                            { text: "", image: "toolbar/icons/sum-64.png", latex: "/prod" },
                            { text: "Another", image: "toolbar/icons/product.png", latex: "/prod" },
                            { text: "Sum", image: "toolbar/icons/sum-64.png", latex: "/prod" },
                            { text: "", image: "toolbar/icons/sum-64.png", latex: "/prod" },
                            { text: "Another", image: "toolbar/icons/product.png", latex: "/prod" },
                        ]
                        },
                        { heading: "Heading 2", items: [
                            { text: "Sum", image: "toolbar/icons/sum-64.png", latex: "/prod" },
                            { text: "", image: "toolbar/icons/sum-64.png", latex: "/prod" },
                            { text: "Another", image: "toolbar/icons/product.png", latex: "/prod" },
                            { text: "Sum", image: "toolbar/icons/sum-64.png", latex: "/prod" },
                            { text: "", image: "toolbar/icons/sum-64.png", latex: "/prod" },
                            { text: "Another", image: "toolbar/icons/product.png", latex: "/prod" },
                            { text: "Sum", image: "toolbar/icons/sum-64.png", latex: "/prod" },
                            { text: "", image: "toolbar/icons/sum-64.png", latex: "/prod" },
                            { text: "Another", image: "toolbar/icons/product.png", latex: "/prod" },
                            { text: "Sum", image: "toolbar/icons/sum-64.png", latex: "/prod" },
                        ]
                        },
                    ]
                    },
                    { text: "Script", image: "toolbar/icons/script-16.png", sections: [
                        { heading: "Heading 1", items: [
                            { text: "Sum", image: "product.png", latex: "/prod" },
                            { text: "Sum", image: "product.png", latex: "/prod" },
                            { text: "Sum", image: "product.png", latex: "/prod" },
                        ]
                        },
                        { heading: "Heading 2", items: [
                            { text: "Sum", image: "product.png", latex: "/prod" },
                            { text: "Sum", image: "product.png", latex: "/prod" },
                            { text: "Sum", image: "product.png", latex: "/prod" },
                        ]
                        },
                    ]
                    },
                    { text: "Radical", image: "toolbar/icons/radical-16.png", sections: [
                        { heading: "Heading 1", items: [
                            { text: "Sum", image: "product.png", latex: "/prod" },
                            { text: "Sum", image: "product.png", latex: "/prod" },
                            { text: "Sum", image: "product.png", latex: "/prod" },
                        ]
                        },
                        { heading: "Heading 2", items: [
                            { text: "Sum", image: "product.png", latex: "/prod" },
                            { text: "Sum", image: "product.png", latex: "/prod" },
                            { text: "Sum", image: "product.png", latex: "/prod" },
                        ]
                        },
                    ]
                    },
                ]
                },
            ]
        },
        { name: "Tab 2", id: "tab2" },
        ]

};

