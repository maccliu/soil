<?php
/**
 * Soil: An underlying PHP framework, under the MIT license.
 *
 * Copyright (c) 2016-2017, Macc Liu <mail@maccliu.com>
 * WITHOUT WARRANTY OF ANY KIND
 */

namespace Soil\IPO;

/**
 * IPO
 *
 * @author Macc Liu <mail@maccliu.com>
 */
abstract class IPO
{
    private $input = null;


    public function setInput(IPO $input)
    {
    }


    public function process()
    {
    }
}

/**
 * EPIPHANY AND ECSTASY! I suddenly understand!
 * Everything is IPO (Input, Process, Output)!
 * And, Input is IPO, Process is IPO, Output is IPO!
 * Let's program with a whole new idea.
 * IPO-oriented!
 * Today is 2016/12/18 Sun!
 *
 * 面向IPO！
 *
 * 这段时间顺着手头的项目一直在研究依赖注入、控制反转、各种工厂方法、设计范式等等这些PHP编程理念，看了
 * 无数开源的PHP框架的源代码，无论是有名的还是没名的，再联想着正在做的项目。然后，昨天的某个时候，突然
 * 就有种顿悟的感觉了。我们写程序想要解决的问题，完全可以用面向IPO的思想来升级面向对象的思想。
 *
 * 用数学表述的话，面向对象是集合论，它描述了一个个实体，但是实体间的关联需要我们自己去连线，是静态的。
 * 而面向IPO就是一个矢量概念，它天生就带上了路径概念，它更突出了有所推进的动态概念，是在传统状态机理论
 * 上进一步抽象。让我想想如何用更清晰的语言来表述这个新编程理论。
 *
 * 2016/12/18 夜，思想上的一点点火花，记之。
 */
